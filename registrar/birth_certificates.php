<?php
session_start();
require_once('../config/config.php');
require_once('../config/checklogin.php');
registrar_check_login();
require_once('../config/codeGen.php');
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
                            <h1 class="m-0 text-left text-dark">Birth Certificates</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Birth Registration Certificates</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="row">

                        <div class="col-12 col-sm-12 col-md-12">
                            <br>
                            <table id="dt-1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Reg No</th>
                                        <th>Name</th>
                                        <th>DOB</th>
                                        <th>Gender</th>
                                        <th>Father</th>
                                        <th>Mother</th>
                                        <th>Place Of Birth</th>
                                        <th>Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM `births_registration` ";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute(); //ok
                                    $res = $stmt->get_result();
                                    $cnt = 1;
                                    while ($births = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <td><?php echo $births->reg_number; ?></td>
                                            <td><?php echo $births->name; ?></td>
                                            <td><?php echo $births->dob; ?></td>
                                            <td><?php echo $births->sex; ?></td>
                                            <td><?php echo $births->fathers_name; ?></td>
                                            <td><?php echo $births->mothers_name; ?></td>
                                            <td><?php echo $births->place_of_birth; ?></td>
                                            <td>
                                                <a class="badge badge-primary" data-toggle="modal" href="#generate-<?php echo $births->id; ?>">
                                                    <i class="fas fa-certificate"></i>
                                                    Generate Certificate
                                                </a>
                                                <!-- Update Births Modal -->
                                                <div class="modal fade" id="generate-<?php echo $births->id; ?>">
                                                    <div class="modal-dialog  modal-xl">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title"><?php echo $births->name; ?> Birth Certificate</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div id="Print_cert">
                                                                    <div class="text-center">
                                                                        <img class="img-fluid" height="150" width="150" src="../public/dist/img/Coat_Of_Arms.png">
                                                                    </div>
                                                                    <div class="row text-center">
                                                                        <div class="col-sm-12">
                                                                            <h3 class="text-bold">REPUBLIC OF KENYA</h3>
                                                                            <h4 class="text-bold">CERTIFICATE OF BIRTH</h4>
                                                                            <h6 class="text-bold">ENTRY NUMBER <?php echo $births->reg_number; ?> </h6>
                                                                        </div>
                                                                    </div>
                                                                    <table class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Full Name</th>
                                                                                <th>Date Of Birth</th>
                                                                                <th>Sex</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td><?php echo $births->name; ?></td>
                                                                                <td><?php echo $births->dob; ?></td>
                                                                                <td><?php echo $births->sex; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    <table class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Name And Surname Of Father</th>
                                                                                <th>Name And Maiden Name Of Mother</th>
                                                                                <th>Place Of Birth</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td><?php echo $births->fathers_name; ?></td>
                                                                                <td><?php echo $births->mothers_name; ?></td>
                                                                                <td><?php echo $births->place_of_birth; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    <table class="table table-bordered table-striped">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Name Of Registering Officer</th>
                                                                                <th>Date Of Registration</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td><?php echo $births->registrar_name; ?></td>
                                                                                <td><?php echo $births->created_at; ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    <hr>
                                                                    <h5 class="text-danger text-center ">
                                                                        I <?php echo $births->registrar_name; ?> District / Assistant Registrar For Machakos District,
                                                                        Hereby Certify That This Certificate Is Compiled From An Entry / Return In The Register Of
                                                                        Births In The District.
                                                                    </h5>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer justify-content-between">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                                <button id="print" onclick="printContent('Print_cert');" class="btn btn-default">Print Certificate</button>
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