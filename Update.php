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

    $stmt = $conn->prepare("UPDATE crud SET Student_Name = ? WHERE Reg_Number = ?");
    $stmt->bind_param("ss", $Student_Name, $Reg_Number);

    if ($stmt->execute()) {
        echo "<script>alert('Student record updated successfully.'); window.location = 'index.php';</script>";
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}

$Reg_Number = $_GET['reg_number'] ?? null;
if ($Reg_Number) {
    $sql = "SELECT * FROM crud WHERE Reg_Number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $Reg_Number);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!isset($data[$row["Reg_Number"]])) {
                $data[$row["Reg_Number"]] = [
                    "Reg_Number" => $row["Reg_Number"],
                    "Student_Name" => $row["Student_Name"],
                    "Profile_Photo" => $row["Profile_Photo"],
                    "Documents" => json_decode($row["Documents"], true),
                    "Subjects" => [],
                    "Marks" => []
                ];
            }
            $data[$row["Reg_Number"]]["Subjects"][] = $row["Subject_Name"];
            $data[$row["Reg_Number"]]["Marks"][] = $row["Mark"];
        }
    } else {
        echo "No student found with Registration Number: " . htmlspecialchars($Reg_Number);
        exit();
    }

    $stmt->close();
} else {
    echo "No student selected.";
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Crud Application - Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            white-space: nowrap;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .profile-photo {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function addSubjectRow() {
            const table = document.getElementById('subjectTable').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();

            const subjectCell = newRow.insertCell(0);
            const markCell = newRow.insertCell(1);
            const actionCell = newRow.insertCell(2);

            subjectCell.innerHTML = '<input type="text" name="Subject_Name[]" class="form-control" required>';
            markCell.innerHTML = '<input type="text" name="Mark[]" class="form-control" required>';
            actionCell.innerHTML = '<button type="button" class="btn btn-danger" onclick="removeSubjectRow(this)">Remove</button>';
        }

        function removeSubjectRow(button) {
            const table = document.getElementById('subjectTable').getElementsByTagName('tbody')[0];
            if (table.rows.length > 1) {
                const row = button.parentNode.parentNode;
                row.parentNode.removeChild(row);
            } else {
                alert('At least one subject is required.');
            }
        }
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mt-5">
                    <div class="card-header">
                        <h1 class="text-center">Student Crud Application - Update</h1>
                    </div>
                    <div class="card-body">
                        <form action="update.php?reg_number=<?= htmlspecialchars($Reg_Number) ?>" method="post"
                            enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="Reg_Number" class="form-label">Reg Number</label>
                                <input type="text" name="Reg_Number" class="form-control" id="Reg_Number"
                                    value="<?= htmlspecialchars($Reg_Number) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="Student_Name" class="form-label">Student Name</label>
                                <input type="text" name="Student_Name" class="form-control" id="Student_Name"
                                    value="<?= htmlspecialchars($data[$Reg_Number]["Student_Name"]) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Profile Photo</label><br>
                                <?php if (!empty($data[$Reg_Number]["Profile_Photo"]) && file_exists($data[$Reg_Number]["Profile_Photo"])): ?>
                                    <img src="<?= htmlspecialchars($data[$Reg_Number]["Profile_Photo"]) ?>" alt="Profile Photo" class="profile-photo">
                                <?php else: ?>
                                    <p>No profile photo uploaded.</p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Documents</label><br>
                                <?php if (!empty($data[$Reg_Number]["Documents"])): ?>
                                    <ul>
                                        <?php foreach ($data[$Reg_Number]["Documents"] as $document): ?>
                                            <li><a href="<?= htmlspecialchars($document) ?>" target="_blank"><?= htmlspecialchars(basename($document)) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No documents uploaded.</p>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subjects</label><br>
                                <table id="subjectTable" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Marks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data[$Reg_Number]["Subjects"] as $key => $subject): ?>
                                            <tr>
                                                <td><input type="text" name="Subject_Name[]" value="<?= htmlspecialchars($subject) ?>" class="form-control" required></td>
                                                <td><input type="text" name="Mark[]" value="<?= htmlspecialchars($data[$Reg_Number]["Marks"][$key]) ?>" class="form-control" required></td>
                                                <td><button type="button" class="btn btn-danger" onclick="removeSubjectRow(this)">Remove</button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-primary" onclick="addSubjectRow()">Add Subject</button>
                            </div>
                            <button type="submit" name="submit" class="btn btn-success">Update Record</button>
                            <a href="index.php" class="btn btn-secondary">Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
