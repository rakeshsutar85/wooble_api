<?php
require_once "conn.php";
require_once "validate.php";
define('UPLOAD_PATH', 'home/images/');
$response = array();
$profileEmail = $_POST['profileEmail'];
if (isset($_GET['apicall'])) {
    switch ($_GET['apicall']) {
        case 'updatecoverpic':
            if (isset($_FILES['pic']['name']) && isset($_POST['profileEmail'])) {
                try {
                    $cover_pic_name = "";
                    $stmt1 = $conn->prepare("SELECT `cover_pic`FROM `user_details` WHERE email=?");
                    $stmt1->bind_param("s", $_POST['profileEmail']);
                    $stmt1->execute();
                    $stmt1->bind_result($cover_pic_name_select);
                    while ($stmt1->fetch()) {
                        $cover_pic_name = $cover_pic_name_select;
                    }
                    unlink(UPLOAD_PATH . $cover_pic_name);
                    move_uploaded_file($_FILES['pic']['tmp_name'], UPLOAD_PATH . $_FILES['pic']['name']);
                    $stmt = $conn->prepare("UPDATE user_info SET cover_pic=? WHERE email=? ");
                    $stmt->bind_param("ss", $_FILES['pic']['name'], $_POST['profileEmail']);
                    if ($stmt->execute()) {
                        $response['error'] = false;
                        $response['message'] = 'File uploaded successfully';
                    } else {
                        throw new Exception("Could not upload file");
                    }
                } catch (Exception $e) {
                    $response['error'] = true;
                    $response['message'] = 'Could not upload file';
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Required params not available";
            }

            break;
        case 'getcoverpic':
            $server_ip = gethostbyname(gethostname());
            $stmt = $conn->prepare("SELECT cover_pic FROM user_info WHERE email='$profileEmail' ");
            $stmt->execute();
            $stmt->bind_result($image);
            $images = array();
            while ($stmt->fetch()) {
                $temp = array();
                $temp['image'] = 'https://' . 'wooble.org/app/home/images/' . $image;
                array_push($images, $temp);
            }
            $response['error'] = false;
            $response['images'] = $images;
            break;
        default:
            $response['error'] = true;
            $response['message'] = 'Invalid api call';
    }
} else {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    exit();
}
header('Content-Type: application/json');
echo json_encode($images);
?>
