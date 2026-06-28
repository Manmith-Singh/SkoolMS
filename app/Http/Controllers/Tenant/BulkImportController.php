<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\Subject;
use App\Models\Tenant\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bulk import of students and teachers from an .xlsx (or .csv) file.
 *
 *   GET  /students/import  -> upload form
 *   POST /students/import  -> process the file
 *   GET  /students/sample  -> download a ready-to-fill template
 *
 *   GET  /teachers/import
 *   POST /teachers/import
 *   GET  /teachers/sample
 *
 * Validation rows are reported back in a flash session; the user sees
 * a per-row "OK / skipped" summary on the form page.
 */
class BulkImportController extends Controller
{
    // -------------------------------------------------------------------
    //  STUDENTS
    // -------------------------------------------------------------------

    public function studentsForm(): View
    {
        return view('students.import');
    }

    public function studentsImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/csv,text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream', 'max:10240'],
        ]);

        $rows    = $this->readRows($request->file('file'));
        $classes = SchoolClass::orderBy('name')->get()->keyBy(fn ($c) => strtolower(trim(($c->name ?? '') . ' ' . ($c->section ?? ''))));

        $created = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($rows as $i => $row) {
            $lineNo = $i + 2; // account for header row

            $data = [
                'admission_no'   => $this->val($row, 'admission_no'),
                'first_name'     => $this->val($row, 'first_name'),
                'last_name'      => $this->val($row, 'last_name'),
                'roll_no'        => $this->val($row, 'roll_no'),
                'dob'            => $this->val($row, 'dob'),
                'gender'         => $this->val($row, 'gender'),
                'email'          => $this->val($row, 'email'),
                'phone'          => $this->val($row, 'phone'),
                'guardian_name'  => $this->val($row, 'guardian_name'),
                'guardian_phone' => $this->val($row, 'guardian_phone'),
                'father_name'    => $this->val($row, 'father_name'),
                'mother_name'    => $this->val($row, 'mother_name'),
                'pen_id'         => $this->val($row, 'pen_id'),
                'caste'          => $this->val($row, 'caste'),
                'aadhaar_number' => $this->val($row, 'aadhaar_number'),
                'status'         => $this->val($row, 'status'),
                'address'        => $this->val($row, 'address'),
                'admission_date' => $this->val($row, 'admission_date'),
                'class_name'     => $this->val($row, 'class_name'),
            ];

            // Lightweight per-row validation.
            $v = Validator::make($data, [
                'first_name'   => ['required', 'string', 'max:100'],
                'last_name'    => ['required', 'string', 'max:100'],
                'email'        => ['nullable', 'email', 'max:191'],
                'admission_no' => ['nullable', 'string', 'max:50', 'unique:tenant.students,admission_no'],
                'dob'          => ['nullable', 'date'],
                'gender'       => ['nullable', 'in:male,female,other'],
                'caste'          => ['nullable', 'string', 'in:OC,BC-A,BC-B,BC-C,BC-D,BC-E,SC,ST,OBC'],
                'aadhaar_number' => ['nullable', 'string', 'size:12'],
                'status'         => ['nullable', 'string', 'in:active,inactive,drop'],
                'admission_date' => ['nullable', 'date'],
            ]);

            if ($v->fails()) {
                $skipped++;
                $errors[] = "Row {$lineNo}: " . implode('; ', $v->errors()->all());
                continue;
            }

            // Resolve class_id by "Name Section" or "Name".
            $classKey = strtolower(trim($data['class_name']));
            $classId  = $classes[$classKey]->id ?? null;
            // Fallback: try just the name part (first word group before space)
            if (! $classId && $data['class_name']) {
                $first   = strtolower(trim(explode(' ', $data['class_name'], 2)[0]));
                $classId = $classes->first(fn ($c) => strtolower(trim($c->name)) === $first)?->id;
            }

            try {
                DB::transaction(function () use ($data, $classId) {
                    Student::create([
                        'admission_no'   => $data['admission_no'] ?: null,
                        'first_name'     => $data['first_name'],
                        'last_name'      => $data['last_name'],
                        'roll_no'        => $data['roll_no'] ?: null,
                        'dob'            => $data['dob'] ?: null,
                        'gender'         => $data['gender'] ?: null,
                        'email'          => $data['email'] ?: null,
                        'phone'          => $data['phone'] ?: null,
                        'guardian_name'  => $data['guardian_name'] ?: null,
                        'guardian_phone' => $data['guardian_phone'] ?: null,
                        'father_name'    => $data['father_name'] ?: null,
                        'mother_name'    => $data['mother_name'] ?: null,
                        'pen_id'         => $data['pen_id'] ?: null,
                        'caste'          => $data['caste'] ?: null,
                        'aadhaar_number' => $data['aadhaar_number'] ?: null,
                        'status'         => $data['status'] ?: 'active',
                        'address'        => $data['address'] ?: null,
                        'admission_date' => $data['admission_date'] ?: null,
                        'class_id'       => $classId,
                    ]);
                });
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$lineNo}: " . $e->getMessage();
            }
        }

        return back()->with('import_result', [
            'type'    => 'students',
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    public function studentsSample(): StreamedResponse
    {
        return $this->streamXlsx('students-template.xlsx', [
            ['admission_no', 'first_name', 'last_name', 'roll_no', 'dob', 'gender',
             'email', 'phone', 'guardian_name', 'guardian_phone',
             'father_name', 'mother_name', 'pen_id', 'caste', 'aadhaar_number', 'status',
             'address', 'admission_date', 'class_name'],
            ['S001', 'John',    'Doe',   '1', '2015-05-10', 'male',
             'john.doe@example.com', '555-0100', 'Mary Doe', '555-0101',
             'Robert Doe', 'Jane Doe', 'PEN123', 'OC', '123456789012', 'active',
             '123 Main St', '2026-04-01', 'Grade 1 A'],
            ['S002', 'Alice',   'Smith', '2', '2015-08-20', 'female',
             'alice.smith@example.com', '555-0200', 'Bob Smith', '555-0201',
             'Tom Smith', 'Lisa Smith', '', 'BC-A', '987654321098', 'active',
             '45 Oak Ave', '2026-04-01', 'Grade 1 A'],
            ['S003', 'Carlos',  'Lopez', '1', '2014-11-02', 'male',
             '', '', 'Elena Lopez', '555-0301',
             '', '', '', '', '', 'active',
             '8 Pine Rd', '2026-04-01', 'Grade 2 A'],
        ]);
    }

    // -------------------------------------------------------------------
    //  TEACHERS
    // -------------------------------------------------------------------

    public function teachersForm(): View
    {
        return view('teachers.import');
    }

    public function teachersImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:text/csv,text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream', 'max:10240'],
        ]);

        $rows     = $this->readRows($request->file('file'));
        $subjects = Subject::orderBy('name')->get()->keyBy(fn ($s) => strtolower(trim($s->code ?? '')));

        $created = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($rows as $i => $row) {
            $lineNo = $i + 2;

            $data = [
                'employee_id'   => $this->val($row, 'employee_id'),
                'first_name'    => $this->val($row, 'first_name'),
                'last_name'     => $this->val($row, 'last_name'),
                'email'         => $this->val($row, 'email'),
                'phone'         => $this->val($row, 'phone'),
                'qualification' => $this->val($row, 'qualification'),
                'hire_date'     => $this->val($row, 'hire_date'),
                'gender'        => $this->val($row, 'gender'),
                'address'       => $this->val($row, 'address'),
                'salary'        => $this->val($row, 'salary'),
                'subject_code'  => $this->val($row, 'subject_code'),
                'status'        => $this->val($row, 'status'),
            ];

            $v = Validator::make($data, [
                'first_name'  => ['required', 'string', 'max:100'],
                'last_name'   => ['required', 'string', 'max:100'],
                'email'       => ['required', 'email', 'max:191', 'unique:tenant.teachers,email'],
                'employee_id' => ['nullable', 'string', 'max:50', 'unique:tenant.teachers,employee_id'],
                'hire_date'   => ['nullable', 'date'],
                'gender'      => ['nullable', 'in:male,female,other'],
                'salary'      => ['nullable', 'numeric', 'min:0'],
                'status'      => ['nullable', 'string', 'in:working,resigned,transfer'],
            ]);

            if ($v->fails()) {
                $skipped++;
                $errors[] = "Row {$lineNo}: " . implode('; ', $v->errors()->all());
                continue;
            }

            $subjectId = $subjects[strtolower(trim($data['subject_code']))]->id ?? null;

            try {
                DB::transaction(function () use ($data, $subjectId) {
                    Teacher::create([
                        'employee_id'   => $data['employee_id'] ?: null,
                        'first_name'    => $data['first_name'],
                        'last_name'     => $data['last_name'],
                        'email'         => $data['email'],
                        'phone'         => $data['phone'] ?: null,
                        'qualification' => $data['qualification'] ?: null,
                        'hire_date'     => $data['hire_date'] ?: null,
                        'gender'        => $data['gender'] ?: null,
                        'address'       => $data['address'] ?: null,
                        'salary'        => $data['salary'] !== null && $data['salary'] !== '' ? $data['salary'] : null,
                        'subject_id'    => $subjectId,
                        'status'        => $data['status'] ?: 'working',
                    ]);
                });
                $created++;
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$lineNo}: " . $e->getMessage();
            }
        }

        return back()->with('import_result', [
            'type'    => 'teachers',
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    public function teachersSample(): StreamedResponse
    {
        return $this->streamXlsx('teachers-template.xlsx', [
            ['employee_id', 'first_name', 'last_name', 'email', 'phone',
             'qualification', 'hire_date', 'gender', 'address', 'salary', 'status', 'subject_code'],
            ['T001', 'Jane',   'Smith',  'jane.smith@school.test',  '555-1000',
             'MSc Mathematics', '2024-01-15', 'female', '12 Elm St', '45000', 'working', 'MTH'],
            ['T002', 'David',  'Khan',   'david.khan@school.test',   '555-1001',
             'BEd English',    '2024-02-01', 'male',   '34 Oak St', '42000', 'working', 'ENG'],
            ['T003', 'Priya',  'Patel',  'priya.patel@school.test',  '555-1002',
             'MSc Physics',    '2024-03-10', 'female', '56 Pine St', '48000', 'working', 'PHY'],
        ]);
    }

    // -------------------------------------------------------------------
    //  Helpers
    // -------------------------------------------------------------------

    /** Get a (case-insensitive, trimmed) column from a row keyed by header. */
    private function val(array $row, string $key): ?string
    {
        if (array_key_exists($key, $row)) {
            $v = $row[$key];
        } else {
            // try case-insensitive
            $lower = strtolower($key);
            $found = null;
            foreach ($row as $k => $v) {
                if (strtolower(trim((string) $k)) === $lower) {
                    $found = $v;
                    break;
                }
            }
            $v = $found;
        }

        if ($v === null) return null;
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    /**
     * Read all rows from an uploaded file as an array of associative arrays.
     * Supports .xlsx, .xls and .csv.
     */
    private function readRows(\Illuminate\Http\UploadedFile $file): array
    {
        $path = $file->getRealPath();
        $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $spreadsheet = IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);
        } else {
            // CSV
            $rows = [];
            if (($h = fopen($path, 'r')) !== false) {
                while (($r = fgetcsv($h)) !== false) {
                    $rows[] = $r;
                }
                fclose($h);
            }
        }

        if (empty($rows)) {
            return [];
        }

        // First row = header
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $data   = [];
        $total  = count($rows);

        for ($i = 1; $i < $total; $i++) {
            $row = $rows[$i];
            // Skip fully blank rows
            if (array_filter($row, fn ($v) => trim((string) $v) !== '') === []) {
                continue;
            }
            $assoc = [];
            foreach ($header as $colIdx => $colName) {
                $assoc[$colName] = $row[$colIdx] ?? null;
            }
            $data[] = $assoc;
        }

        return $data;
    }

    /**
     * Stream an .xlsx file with the given rows.
     *
     * @param  string  $filename  download name
     * @param  array   $rows      first row is treated as header
     */
    private function streamXlsx(string $filename, array $rows): StreamedResponse
    {
        $ss = new Spreadsheet();
        $ss->getActiveSheet()->fromArray($rows);

        // Style the header row
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FF2A4F87']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $colCount = count($rows[0] ?? []);
        if ($colCount > 0) {
            $ss->getActiveSheet()
                ->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount) . '1')
                ->applyFromArray($headerStyle);
        }

        // Auto-size columns
        foreach (range(1, $colCount) as $col) {
            $ss->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($ss) {
            (new Xlsx($ss))->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
