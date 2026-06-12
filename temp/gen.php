
require __DIR__ . '/vendor/autoload.php';
$ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$ss->getActiveSheet()->fromArray([
    ['admission_no', 'first_name', 'last_name', 'roll_no', 'dob', 'gender',
     'email', 'phone', 'guardian_name', 'guardian_phone', 'address',
     'admission_date', 'class_name'],
    ['NEW6868871', 'Fresh', 'One',   '1', '2015-01-01', 'male',
     'f1-686887@x.test', '1111', 'G1', '1112', '1 Test St', '2026-04-01', ''],
    ['NEW6868872', 'Fresh', 'Two',   '2', '2015-02-02', 'female',
     'f2-686887@x.test', '2222', 'G2', '2222', '2 Test St', '2026-04-01', ''],
    ['NEW6868873', 'Fresh', 'Three', '3', '2015-03-03', 'male',
     'f3-686887@x.test', '3333', 'G3', '3333', '3 Test St', '2026-04-01', ''],
]);
(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('temp/fresh_students.xlsx');
echo "ok";
