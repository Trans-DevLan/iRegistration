<?php
session_start();
require_once('../config/config.php');
require_once('../config/checklogin.php');
require_once('../config/codeGen.php');
registrar_check_login();


/* Import Death Registration Files From Excel Sheets */

use DevLanDataAPI\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require_once('../config/DataSource.php');
$db = new DataSource();
$conn = $db->getConnection();
require_once('../vendor/autoload.php');

if (isset($_POST["upload"])) {

    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    /* Where Magic Happens */

    if (in_array($_FILES["file"]["type"], $allowedFileType)) {

        $targetPath = '../public/uploads/xls/' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);

        for ($i = 1; $i <= $sheetCount; $i++) {

            $id = "";
            if (isset($spreadSheetAry[$i][0])) {
                $id = mysqli_real_escape_string($conn, $spreadSheetAry[$i][0]);
            }

            $reg_number = "";
            if (isset($spreadSheetAry[$i][1])) {
                $reg_number = mysqli_real_escape_string($conn, $spreadSheetAry[$i][1]);
            }

            $registrar_name = "";
            if (isset($spreadSheetAry[$i][2])) {
                $registrar_name = mysqli_real_escape_string($conn, $spreadSheetAry[$i][2]);
            }

            $name = "";
            if (isset($spreadSheetAry[$i][3])) {
                $name = mysqli_real_escape_string($conn, $spreadSheetAry[$i][3]);
            }

            $dob = "";
            if (isset($spreadSheetAry[$i][4])) {
                $dob = mysqli_real_escape_string($conn, $spreadSheetAry[$i][4]);
            }

            $age  = "";
            if (isset($spreadSheetAry[$i][5])) {
                $age  = mysqli_real_escape_string($conn, $spreadSheetAry[$i][5]);
            }

            $sex = "";
            if (isset($spreadSheetAry[$i][6])) {
                $sex = mysqli_real_escape_string($conn, $spreadSheetAry[$i][6]);
            }

            $occupation = "";
            if (isset($spreadSheetAry[$i][7])) {
                $occupation = mysqli_real_escape_string($conn, $spreadSheetAry[$i][7]);
            }

            $place_of_death = "";
            if (isset($spreadSheetAry[$i][8])) {
                $place_of_death = mysqli_real_escape_string($conn, $spreadSheetAry[$i][8]);
            }

            $tribe = "";
            if (isset($spreadSheetAry[$i][9])) {
                $tribe = mysqli_real_escape_string($conn, $spreadSheetAry[$i][9]);
            }

            $month_reg = "";
            if (isset($spreadSheetAry[$i][10])) {
                $month_reg = mysqli_real_escape_string($conn, $spreadSheetAry[$i][10]);
            }

            $year_reg = "";
            if (isset($spreadSheetAry[$i][11])) {
                $year_reg = mysqli_real_escape_string($conn, $spreadSheetAry[$i][11]);
            }

            $created_at = "";
            if (isset($spreadSheetAry[$i][12])) {
                $created_at = mysqli_real_escape_string($conn, $spreadSheetAry[$i][12]);
            }


            if (!empty($name) || !empty($dob) || !empty($place_of_death) || !empty($age) || !empty($sex)) {
                $query = "INSERT INTO deaths_registration (id, reg_number, registrar_name, name, dob, age, sex, occupation, place_of_death, tribe, month_reg, year_reg, created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $paramType = "sssssssssssss";
                $paramArray = array(
                    $id,
                    $reg_number,
                    $registrar_name,
                    $name,
                    $dob,
                    $age,
                    $sex,
                    $occupation,
                    $place_of_death,
                    $tribe,
                    $month_reg,
                    $year_reg,
                    $created_at
                );
                $insertId = $db->insert($query, $paramType, $paramArray);
                if (!empty($insertId)) {
                    $err = "Error Occured While Importing Data";
                } else {
                    $success = "Data Imported" && header("refresh:1; url=deaths_registration.php");
                }
            }
        }
    } else {
        $info = "Invalid File Type. Upload Excel File.";
    }
}

/* Add Death Registration  */
if (isset($_POST['report_death'])) {
    //Error Handling and prevention of posting double entries
    $error = 0;

    if (isset($_POST['name']) && !empty($_POST['name'])) {
        $name = mysqli_real_escape_string($mysqli, trim($_POST['name']));
    } else {
        $error = 1;
        $err = "Child Name Cannot Be Empty";
    }
    if (isset($_POST['dob']) && !empty($_POST['dob'])) {
        $dob = mysqli_real_escape_string($mysqli, trim($_POST['dob']));
    } else {
        $error = 1;
        $err = "Date Of Birth Cannot Be Empty";
    }
    if (isset($_POST['sex']) && !empty($_POST['sex'])) {
        $sex = mysqli_real_escape_string($mysqli, trim($_POST['sex']));
    } else {
        $error = 1;
        $err = "Gender Cannot Be Empty";
    }

    /* System Generated Registration Number -> To Edit This File Check Codegen.php File Under Configs*/
    if (isset($_POST['reg_number']) && !empty($_POST['reg_number'])) {
        $reg_number = mysqli_real_escape_string($mysqli, trim($_POST['reg_number']));
    } else {
        $error = 1;
        $err = "Registration Number Cannot Be Empty";
    }

    if (!$error) {
        //prevent Double entries
        $sql = "SELECT * FROM  deaths_registration WHERE  reg_number='$reg_number'  ";
        $res = mysqli_query($mysqli, $sql);
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            if ($reg_number == $row['reg_number']) {
                $err =  "A Death Record With That Registration Number Exists";
            }
        } else {
            $id = $_POST['id'];
            $reg_number = $_POST['reg_number'];
            $registrar_name = $_POST['registrar_name'];
            $name = $_POST['name'];
            $dob  = $_POST['dob'];
            $sex = $_POST['sex'];
            $age = $_POST['age'];
            $occupation = $_POST['occupation'];
            $place_of_death = $_POST['place_of_death'];
            $tribe = $_POST['tribe'];
            $month_reg = $_POST['month_reg'];
            $year_reg = $_POST['year_reg'];
            $created_at = date('d M Y');

            $query = "INSERT INTO deaths_registration (id, reg_number, registrar_name, name, dob, age, sex, occupation, place_of_death, tribe, month_reg, year_reg, created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $rc = $stmt->bind_param('sssssssssssss', $id, $reg_number, $registrar_name, $name, $dob, $age, $sex, $occupation, $place_of_death, $tribe, $month_reg, $year_reg, $created_at);
            $stmt->execute();
            if ($stmt) {
                $success = "Added" && header("refresh:1; url=deaths_registration.php");
            } else {
                //inject alert that profile update task failed
                $info = "Please Try Again Or Try Later";
            }
        }
    }
}

if (isset($_POST['update_death'])) {
    /* Handle Death Records Update Logic */
    $reg_number = $_POST['reg_number'];
    $name = $_POST['name'];
    $dob  = $_POST['dob'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];
    $occupation = $_POST['occupation'];
    $place_of_death = $_POST['place_of_death'];
    $tribe = $_POST['tribe'];
    $query = "UPDATE deaths_registration  SET  name =? ,dob =? ,sex =? ,age =? ,occupation =?,place_of_death =?,tribe =? WHERE reg_number =?";
    $stmt = $conn->prepare($query);
    $rc = $stmt->bind_param('ssssssss',  $name, $dob, $sex, $age, $occupation, $place_of_death, $tribe, $reg_number);
    $stmt->execute();
    if ($stmt) {
        $success = "Records Updated" && header("refresh:1; url=deaths_registration.php");
    } else {
        //inject alert that task failed
        $info = "Please Try Again Or Try Later";
    }
}



/*if (isset($_GET['delete_death_record'])) {
    /* Handle Death Records Deletion Here */
    /*$id = $_GET['delete_death_record'];
    $adn = "DELETE FROM deaths_registration WHERE id=?";
    $stmt = $conn->prepare($adn);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $stmt->close();
    if ($stmt) {
        $success = "Removed permantly" && header("refresh:1; url=deaths_registration.php");
    } else {
        //inject alert that task failed
        $info = "Please Try Again Or Try Later";
    }
}*/


require_once('../partials/head.php');

?>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <?php require_once('../partials/navigation.php'); ?>
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-left text-dark">Mortality Registration Records</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Mortality Registrations</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="text-right">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#import-modal">Import Death Records </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add-modal">Add Death Record</button>
                    </div>
                    <!-- Import Births Registration Modal -->
                    <div class="modal fade" id="import-modal">
                        <div class="modal-dialog  modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        Allowed file types: XLS, XLSX. Please, <a href="public/templates/sample_deaths_file.xlsx">Download</a> The Sample File.
                                    </h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Form -->
                                    <form method="post" enctype="multipart/form-data" role="form">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="exampleInputFile">Select File</label>
                                                    <div class="input-group">
                                                        <div class="custom-file">
                                                            <input required name="file" accept=".xls,.xlsx" type="file" class="custom-file-input" id="exampleInputFile">
                                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" name="upload" class="btn btn-primary">Upload File</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Import Birth Registration Modal -->

                    <!-- Add Birth Registration  Modal -->
                    <div class="modal fade" id="add-modal">
                        <div class="modal-dialog  modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Fill All Given Fields</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!-- Form -->
                                    <form method="post" enctype="multipart/form-data" role="form">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="">Registration Number</label>
                                                    <input type="text" required name="reg_number" value="<?php echo $a; ?>-<?php echo $b; ?>" class="form-control" id="exampleInputEmail1">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="">Registrar Name</label>
                                                    <select type="text" required name="registrar_name" class="form-control basic">
                                                        <?php
                                                        /* Pull A List Of All Registers */
                                                        $ret = "SELECT * FROM `users` ";
                                                        $stmt = $mysqli->prepare($ret);
                                                        $stmt->execute(); //ok
                                                        $res = $stmt->get_result();
                                                        $cnt = 1;
                                                        while ($users = $res->fetch_object()) {
                                                        ?>
                                                            <option><?php echo $users->name; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <label for="">Deceased Full Name</label>
                                                    <input type="text" required name="name" class="form-control" id="exampleInputEmail1">
                                                    <!-- Hide This -->
                                                    <input type="hidden" required name="id" value="<?php echo $ID; ?>" class="form-control">
                                                    <input type="hidden" required name="month_reg" value="<?php echo date('M'); ?>" class="form-control">
                                                    <input type="hidden" required name="year_reg" value="<?php echo date('Y'); ?>" class="form-control">
                                                </div>

                                                <div class="form-group col-md-4">
                                                    <label for="">Deceased Date Of Birth</label>
                                                    <input type="text" required name="dob" class="form-control">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="">Deceased Age</label>
                                                    <input type="text" required name="age" class="form-control">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="">Deceased Gender</label>
                                                    <select type="text" required name="sex" class="form-control basic">
                                                        <option>Male</option>
                                                        <option>Female</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="">Deceased Occupation </label>
                                                    <input type="text" required name="occupation" class="form-control">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="">Deceased Tribe</label>
                                                    <input type="text" required name="tribe" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <label for="exampleInputPassword1">Place Of Death</label>
                                                    <textarea required name="place_of_death" rows="3" class="form-control"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" name="report_death" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End  Modal -->
                    <div class="row">

                        <div class="col-12 col-sm-12 col-md-12">
                            <br>
                            <table id="dt-1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Reg No</th>
                                        <th>Name</th>
                                        <th>DOB</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Occupation</th>
                                        <th>Tribe</th>
                                        <th>Place Of Death</th>
                                        <th>Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM `deaths_registration` ";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute(); //ok
                                    $res = $stmt->get_result();
                                    $cnt = 1;
                                    while ($deaths = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <td><?php echo $deaths->reg_number; ?></td>
                                            <td><?php echo $deaths->name; ?></td>
                                            <td><?php echo $deaths->dob; ?></td>
                                            <td><?php echo $deaths->age; ?></td>
                                            <td><?php echo $deaths->sex; ?></td>
                                            <td><?php echo $deaths->occupation; ?></td>
                                            <td><?php echo $deaths->tribe; ?></td>
                                            <td><?php echo $deaths->place_of_death; ?></td>
                                            <td>
                                                <a class="badge badge-primary" data-toggle="modal" href="#update-<?php echo $deaths->id; ?>">
                                                    <i class="fas fa-edit"></i>
                                                    Update
                                                </a>
                                                <!-- Update Births Modal -->
                                                <div class="modal fade" id="update-<?php echo $deaths->id; ?>">
                                                    <div class="modal-dialog  modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Update <?php echo $deaths->reg_number; ?> Record</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <!-- Form -->
                                                                <!-- Form -->
                                                                <form method="post" enctype="multipart/form-data" role="form">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="form-group col-md-6">

                                                                                <input type="text" hidden required name="reg_number" value="<?php echo $deaths->reg_number; ?>" class="form-control" id="exampleInputEmail1">
                                                                            </div>

                                                                            <div class="form-group col-md-12">
                                                                                <label for="">Deceased Full Name</label>
                                                                                <input type="text" required name="name" value="<?php echo $deaths->name; ?>" class="form-control" id="exampleInputEmail1">
                                                                            </div>

                                                                            <div class="form-group col-md-4">
                                                                                <label for="">Deceased Date Of Birth</label>
                                                                                <input type="text" required name="dob" value="<?php echo $deaths->dob; ?>" class="form-control">
                                                                            </div>
                                                                            <div class="form-group col-md-4">
                                                                                <label for="">Deceased Age</label>
                                                                                <input type="text" required name="age" value="<?php echo $deaths->age; ?>" class="form-control">
                                                                            </div>
                                                                            <div class="form-group col-md-4">
                                                                                <label for="">Deceased Gender</label>
                                                                                <select type="text" required name="sex" class="form-control basic">
                                                                                    <option selected><?php echo $deaths->sex; ?></option>
                                                                                    <option value="male">Male</option>
                                                                                    <option value="male">Female</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="form-group col-md-6">
                                                                                <label for="">Deceased Occupation </label>
                                                                                <input type="text" required name="occupation" value="<?php echo $deaths->occupation; ?>" class="form-control">
                                                                            </div>
                                                                            <div class="form-group col-md-6">
                                                                                <label for="">Deceased Tribe</label>
                                                                                <input type="text" required name="tribe" value="<?php echo $deaths->tribe; ?>" class="form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="form-group col-md-12">
                                                                                <label for="exampleInputPassword1">Place Of Death</label>
                                                                                <textarea required name="place_of_death" value="<?php echo $deaths->place_of_death; ?>" rows="3" class="form-control"></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <button type="submit" name="update_death" class="btn btn-primary">Save changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="modal-footer justify-content-between">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End Modal -->


                                               
                                                
                                                
                                            </td>
                                        </tr>
                                    <?php
                                    } ?>
                                </tbody>
                            </table>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php require_once('../partials/footer.php'); ?>
    </div>
    <!-- REQUIRED SCRIPTS -->
    <?php require_once('../partials/scripts.php'); ?>
</body>


</html>