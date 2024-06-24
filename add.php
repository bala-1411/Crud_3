<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "test_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $Reg_Number = mysqli_real_escape_string($conn, $_POST['Reg_Number']);
    $Student_Name = mysqli_real_escape_string($conn, $_POST['Student_Name']);
    $Subject_Names = $_POST['Subject_Name'];
    $Marks = $_POST['Mark'];

    $uploadDir = 'uploads/';
    $profilePhoto = $_FILES['Profile_Photo'];
    $profilePhotoPath = '';

    if ($profilePhoto['error'] === UPLOAD_ERR_OK) {
        $profilePhotoPath = $uploadDir . basename($profilePhoto['name']);
        if (!move_uploaded_file($profilePhoto['tmp_name'], $profilePhotoPath)) {
            echo "Error uploading profile photo.";
            exit();
        }
    } else {
        echo "Error uploading profile photo: " . $profilePhoto['error'];
        exit();
    }

    $documentPaths = [];
    if (!empty($_FILES['Documents']['name'][0])) {
        foreach ($_FILES['Documents']['tmp_name'] as $key => $tmp_name) {
            $filePath = $uploadDir . basename($_FILES['Documents']['name'][$key]);
            if (!move_uploaded_file($tmp_name, $filePath)) {
                echo "Error uploading document: " . $_FILES['Documents']['name'][$key];
                exit();
            }
            $documentPaths[] = $filePath;
        }
    }

    $documentPathsJson = json_encode($documentPaths);

    $query = "INSERT INTO crud (Reg_Number, Student_Name, Subject_Name, Mark, Profile_Photo, Documents)
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);

    for ($i = 0; $i < count($Subject_Names); $i++) {
        $subjectName = mysqli_real_escape_string($conn, $Subject_Names[$i]);
        $mark = mysqli_real_escape_string($conn, $Marks[$i]);
        
        $stmt->bind_param("ssssss", $Reg_Number, $Student_Name, $subjectName, $mark, $profilePhotoPath, $documentPathsJson);
        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
            exit();
        }
    }

    $stmt->close();
    $conn->close();

    echo "<script>alert('Registration successful.'); window.location = 'index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Crud Application - Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script>
    function addSubjectMarkFields() {
        var container = document.getElementById('subjectMarkContainer');
        var div = document.createElement('div');
        div.className = 'form-group row mb-2';

        div.innerHTML = `
            <label for="Subject_Name[]" class="col-sm-2 col-form-label">Subject Name</label>
            <div class="col-sm-4">
                <input type="text" name="Subject_Name[]" class="form-control"
                    placeholder="Enter Subject Name" required>
            </div>
            <label for="Mark[]" class="col-sm-1 col-form-label">Mark</label>
            <div class="col-sm-3">
                <input type="text" name="Mark[]" class="form-control" placeholder="Enter Mark" required>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-success"
                    onclick="addSubjectMarkFields()">+</button>
                <button type="button" class="btn btn-danger" onclick="removeSubjectMarkFields(this)"
                    disabled>-</button>
            </div>
        `;
        container.appendChild(div);

        var rows = container.querySelectorAll('.form-group.row.mb-2');
        if (rows.length > 1) {
            var lastRow = rows[rows.length - 1];
            var removeButton = lastRow.querySelector('.btn-danger');
            if (removeButton) {
                removeButton.removeAttribute('disabled');
            }
        }
    }

    function removeSubjectMarkFields(button) {
        var container = document.getElementById('subjectMarkContainer');
        if (container.childElementCount > 1) {
            container.removeChild(button.parentElement.parentElement);
        } else {
            alert('At least one subject is required.');
        }

        var rows = container.querySelectorAll('.form-group.row.mb-2');
        if (rows.length === 1) {
            var lastRow = rows[0];
            var removeButton = lastRow.querySelector('.btn-danger');
            if (removeButton) {
                removeButton.setAttribute('disabled', 'disabled');
            }
        }
    }

    function validateForm() {
        var subjectInputs = document.getElementsByName('Subject_Name[]');
        var markInputs = document.getElementsByName('Mark[]');
        var regNumberInput = document.getElementsByName('Reg_Number')[0];
        var studentNameInput = document.getElementsByName('Student_Name')[0];

        if (!/^\d+$/.test(regNumberInput.value.trim())) {
            alert('Registration Number must be a number.');
            return false;
        }

        if (!/^[\w\s]+$/.test(studentNameInput.value.trim())) {
            alert('Student Name should not have special characters except space.');
            return false;
        }

        for (var i = 0; i < markInputs.length; i++) {
            if (!/^\d+$/.test(markInputs[i].value.trim())) {
                alert('Mark should be a number.');
                return false;
            }
        }

        for (var i = 0; i < subjectInputs.length; i++) {
            if (subjectInputs[i].value.trim() === '' || markInputs[i].value.trim() === '') {
                alert('Please fill in all subject and mark fields.');
                return false;
            }
        }

        return true;
    }
    </script>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h1>Student Crud Application - Add Student</h1>
                    </div>
                    <div class="card-body">
                        <form action="add.php" method="post" enctype="multipart/form-data"
                            onsubmit="return validateForm()">
                            <div class="form-group">
                                <label for="Reg_Number">Registration Number</label>
                                <input type="text" name="Reg_Number" class="form-control"
                                    placeholder="Enter Registration Number" required pattern="\d+">
                            </div>
                            <div class="form-group">
                                <label for="Student_Name">Student Name</label>
                                <input type="text" name="Student_Name" class="form-control"
                                    placeholder="Enter Student Name" required pattern="[\w\s]+">
                            </div>
                            <div class="form-group">
                                <label for="Profile_Photo">Profile Photo</label>
                                <input type="file" name="Profile_Photo" class="form-control" accept="image/*" required>
                            </div>
                            <div class="form-group">
                                <label for="Documents">Documents (PDF/Excel)</label>
                                <input type="file" name="Documents[]" class="form-control" accept=".pdf,.xls,.xlsx"
                                    multiple>
                            </div>
                            <div id="subjectMarkContainer">
                                <div class="form-group row mb-2">
                                    <label for="Subject_Name[]" class="col-sm-2 col-form-label">Subject Name</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="Subject_Name[]" class="form-control"
                                            placeholder="Enter Subject Name" required>
                                    </div>
                                    <label for="Mark[]" class="col-sm-1 col-form-label">Mark</label>
                                    <div class="col-sm-3">
                                        <input type="text" name="Mark[]" class="form-control"
                                            placeholder="Enter Mark" required>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-success"
                                            onclick="addSubjectMarkFields()">+</button>
                                        <button type="button" class="btn btn-danger" disabled>-</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-10 offset-sm-2">
                                    <button type="submit" class="btn btn-primary" name="submit">Register</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
