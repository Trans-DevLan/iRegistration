<?php
session_start();
require_once('../config/config.php');
require_once('../config/checklogin.php');
require_once('../config/codeGen.php');

if (isset($_POST['add_auth_permission'])) {
    /* Give User Auth Permission */
    $error = 0;

    if (isset($_POST['auth_email']) && !empty($_POST['auth_email'])) {
        $auth_email = mysqli_real_escape_string($mysqli, trim($_POST['auth_email']));
    } else {
        $error = 1;
        $err = "Email Address Cannot Be Empty";
    }
    if (isset($_POST['auth_password']) && !empty($_POST['auth_password'])) {
        $auth_password = mysqli_real_escape_string($mysqli, trim(sha1(md5($_POST['auth_password']))));
    } else {
        $error = 1;
        $err = "Auth Password Cannot Be Empty";
    }

    if (isset($_POST['auth_permission']) && !empty($_POST['auth_permission'])) {
        $auth_permission = mysqli_real_escape_string($mysqli, trim($_POST['auth_permission']));
    } else {
        $error = 1;
        $err = "Auth Permissions Cannot Be Empty";
    }
    if (isset($_POST['auth_id']) && !empty($_POST['auth_id'])) {
        $auth_id = mysqli_real_escape_string($mysqli, trim($_POST['auth_id']));
    } else {
        $error = 1;
        $err = "Auth ID Cannot Be Empty";
    }

    if (isset($_POST['auth_status']) && !empty($_POST['auth_status'])) {
        $auth_status = mysqli_real_escape_string($mysqli, trim($_POST['auth_status']));
    } else {
        $error = 1;
        $err = "Auth Status Cannot Be Empty";
    }


    if (!$error) {
        /* 
            Auth Permissions Logic
            1. Insert User Auth Details On Auth Table
            2. Update Users Table Add Auth ID And Auth Status
        */

        $authDetails = "INSERT INTO authentication (auth_id, auth_permission, auth_email, auth_password) VALUES(?,?,?,?)";
        $userAuthStatus = "UPDATE users SET auth_id = ?, auth_status = ? WHERE email = ?";

        $authDetailsStmt = $mysqli->prepare($authDetails);
        $userAuthStatusStmt = $mysqli->prepare($userAuthStatus);

        $rc = $authDetailsStmt->bind_param('ssss', $auth_id, $auth_permission, $auth_email, $auth_password);
        $rc = $userAuthStatusStmt->bind_param('sss', $auth_id, $auth_status, $auth_email);

        $authDetailsStmt->execute();
        $userAuthStatusStmt->execute();

        if ($authDetailsStmt && $userAuthStatusStmt) {

            $success = "Auth Permissions Added" && header("refresh:1; url=user_permissions.php");
        } else {
            //Inject alert
            $info = "Please Try Again Or Try Later";
        }
    }
}


if (isset($_GET['revoke'])) {
    /* 
        Revoke User Auth Crendetials
        Logic
        1. Delete Auth User Details From Auth table
        2. Delete Auth ID And Auth Status From Users Table
    */

    $auth_id = $_GET['revoke'];
    $auth_email = $_GET['auth_email'];
    $auth_status = $_GET['auth_status'];

    $revokeAuth = "DELETE FROM authentication  WHERE auth_id =?";
    $clearAuthStatus = "UPDATE users SET auth_id ='', auth_status = ? WHERE email = ?";

    $revokeAuthStmt = $mysqli->prepare($revokeAuth);
    $clearAuthStatusStmt = $mysqli->prepare($clearAuthStatus);

    $revokeAuthStmt->bind_param('s', $auth_id);
    $clearAuthStatusStmt->bind_param('ss', $auth_status, $auth_email);

    $revokeAuthStmt->execute();
    $clearAuthStatusStmt->execute();

    $revokeAuthStmt->close();
    $clearAuthStatusStmt->close();

    if ($revokeAuthStmt && $clearAuthStatusStmt) {

        $success = "Revoked Auth Permissions" && header("refresh:1; url=user_permissions.php");
    } else {
        //inject alert that task failed
        $info = "Please Try Again Or Try Later";
    }
}

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
                            <h1 class="m-0 text-left text-dark">Users With Auth Permissions</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">User Auth Permissions</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="text-right">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add-modal">Give Auth Permissions</button>
                    </div>
                    <!-- Add Registras Auth Permissions Modal -->
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
                                                <div class="form-group col-md-12">
                                                    <label for="">User Email</label>
                                                    <select class='form-control basic' name="auth_email">
                                                        <option selected>Select User Email Address</option>
                                                        <?php
                                                        $ret = "SELECT * FROM `users` WHERE auth_status != 'Can_Login' ";
                                                        $stmt = $mysqli->prepare($ret);
                                                        $stmt->execute(); //ok
                                                        $res = $stmt->get_result();
                                                        while ($user = $res->fetch_object()) {
                                                        ?>
                                                            <option><?php echo $user->email; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <!-- Hide This -->
                                                    <input type="hidden" required name="auth_id" value="<?php echo $ID; ?>" class="form-control">
                                                    <input type="hidden" required name="auth_status" value="Can_Login" class="form-control">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-8">
                                                    <label for="">Auth Password</label>
                                                    <input type="password" required name="auth_password" class="form-control">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="">Auth Permissions</label>
                                                    <select type="text" required name="auth_permission" class="form-control basic">
                                                        <option>Registrar</option>
                                                        <option>Administrator</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" name="add_auth_permission" class="btn btn-primary">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- End Add Registras Auth Permissions Modal -->

                    <div class="row">

                        <div class="col-12 col-sm-12 col-md-12">
                            <br>
                            <table id="dt-1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>ID No</th>
                                        <th>Gender</th>
                                        <th>Email</th>
                                        <th>Phone No</th>
                                        <th>Address</th>
                                        <th>Manage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ret = "SELECT * FROM `users` WHERE auth_status = 'Can_Login' ";
                                    $stmt = $mysqli->prepare($ret);
                                    $stmt->execute(); //ok
                                    $res = $stmt->get_result();
                                    while ($AuthUsers = $res->fetch_object()) {
                                    ?>
                                        <tr>
                                            <td><?php echo $AuthUsers->name; ?></td>
                                            <td><?php echo $AuthUsers->national_idno; ?></td>
                                            <td><?php echo $AuthUsers->sex; ?></td>
                                            <td><?php echo $AuthUsers->email; ?></td>
                                            <td><?php echo $AuthUsers->phone; ?></td>
                                            <td><?php echo $AuthUsers->addr; ?></td>
                                            <td>

                                                <a class="badge badge-danger" data-toggle="modal" href="#revoke-<?php echo $AuthUsers->id; ?>">
                                                    <i class="fas fa-trash"></i>
                                                    Revoke Auth Permissions
                                                </a>
                                                <!-- Revoke Auth Permission Modal -->
                                                <div class="modal fade" id="revoke-<?php echo $AuthUsers->id; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLabel">CONFIRM</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body text-center text-danger">
                                                                <h4>Revoke <br> <?php echo $AuthUsers->name; ?> <br> Authentication Permissions ?</h4>
                                                                <br>
                                                                <button type="button" class="text-center btn btn-success" data-dismiss="modal">No</button>
                                                                <a href="user_permissions.php?revoke=<?php echo $AuthUsers->auth_id; ?>&auth_status=Revoked&auth_email=<?php echo $AuthUsers->email; ?>" class="text-center btn btn-danger"> Revoke </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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