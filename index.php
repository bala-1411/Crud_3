<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "test_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT Reg_Number, Student_Name FROM crud";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Crud Application</title>
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

        .text-light {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card mt-5">
                    <div class="card-header">
                        <h1 class="text-center">Student Crud Application</h1>
                    </div>
                    <div class="card-body">
                        <a href="add.php" class="btn btn-success mb-3 text-light">ADD</a>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Reg_Number</th>
                                        <th scope="col">Student_Name</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row["Reg_Number"]) ?></td>
                                            <td><?= htmlspecialchars($row["Student_Name"]) ?></td>
                                            <td>
                                                <a href="update.php?reg_number=<?= htmlspecialchars($row["Reg_Number"]) ?>" class="btn btn-success text-light">Update</a>
                                                <form action="delete.php" method="POST" style="display:inline-block;">
                                                    <input type="hidden" name="reg_number" value="<?= htmlspecialchars($row["Reg_Number"]) ?>">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
