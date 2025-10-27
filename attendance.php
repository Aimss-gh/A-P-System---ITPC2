<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}


$conn = mysqli_connect("sql105.infinityfree.com", "if0_40255781", "clarabal2004", "if0_40255781_APS");

if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


if(isset($_POST['submit_attendance'])) {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time_in = mysqli_real_escape_string($conn, $_POST['time_in']);
    $time_out = mysqli_real_escape_string($conn, $_POST['time_out']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $hours_worked = 0;
    if($time_in && $time_out) {
        $start = strtotime($time_in);
        $end = strtotime($time_out);
        $hours_worked = round(($end - $start) / 3600, 2);
    }
    
 
    $overtime_hours = 0;
    if($hours_worked > 8) {
        $overtime_hours = $hours_worked - 8;
        $hours_worked = 8;
    }
    
    $query = "INSERT INTO attendance (employee_id, date, time_in, time_out, hours_worked, overtime_hours, status, remarks) 
              VALUES ('$employee_id', '$date', '$time_in', '$time_out', '$hours_worked', '$overtime_hours', '$status', '$remarks')
              ON DUPLICATE KEY UPDATE 
              time_in = '$time_in', 
              time_out = '$time_out', 
              hours_worked = '$hours_worked',
              overtime_hours = '$overtime_hours',
              status = '$status',
              remarks = '$remarks'";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success_msg'] = "Attendance recorded successfully!";
    } else {
        $_SESSION['error_msg'] = "Error: " . mysqli_error($conn);
    }
    
    header("Location: attendance.php");
    exit();
}


if(isset($_GET['delete_id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $delete_query = "DELETE FROM attendance WHERE id = '$delete_id'";
    
    if(mysqli_query($conn, $delete_query)) {
        $_SESSION['success_msg'] = "Attendance record deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting record: " . mysqli_error($conn);
    }
    
    header("Location: attendance.php");
    exit();
}

$employees_query = "SELECT * FROM employees WHERE status = 'active' ORDER BY first_name ASC";
$employees_result = mysqli_query($conn, $employees_query);


$attendance_query = "SELECT a.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.department 
                     FROM attendance a 
                     LEFT JOIN employees e ON a.employee_id = e.id 
                     ORDER BY a.date DESC, a.time_in DESC 
                     LIMIT 100";
$attendance_result = mysqli_query($conn, $attendance_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - APS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            background-image: url('img/gradient.jpg');
        }
        
        .navbar {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);*/
            position: fixed;
            width: 300px;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(100px);
            border-left: 10px rgba(255, 255, 255, 0.5);
            transition: 0.5s;
            overflow: hidden;
        }
        
        .navbar ul {
            position: relative;
            left: 0;
            width: 100%;
            margin: 10px 0 0px 0;
        }
        
        .navbar.active {
            width: 70px;
        }
        
        .navbar ul li {
            position: relative;
            padding-top: 20px;
            width: 100%;
            list-style: none;
            border-top-left-radius: 35px;
            border-bottom-left-radius: 35px;
        }
        
        .navbar ul li:hover {
            background-color: white;
        }
        
        .navbar ul li:nth-child(1) {
            margin-top: 40px;
            pointer-events: none;
        }
        
        .navbar ul li a {
            position: relative;
            display: block;
            width: 100%;
            display: flex;
            text-decoration: none;
            color: rgb(0, 0, 0);
        }
        
        .navbar.active {
            width: 60px;
        }
        
        .navbar ul li:hover a {
            color: rgb(0, 0, 0);
        }
        
        .navbar ul li .icon {
            position: relative;
            display: block;
            min-width: 60px;
            height: 60px;
            line-height: 40px;
            text-align: center;
        }
        
        .navbar.active {
            width: 60px;
        }
        
        .navbar ul li .icon ion-icon {
            font-size: 1.75rem;
        }
        
        .navbar ul li a.title {
            position: relative;
            display: block;
            padding: 20px;
            margin-bottom: 100px;
            height: 60px;
            line-height: 60px;
            text-align: start;
            white-space: nowrap;
        }
        
        .navbar ul li:hover a::before {
            content: '';
            position: absolute;
            right: 0;
            top: -70px;
            width: 50px;
            height: 50px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 35px 35px 0 10px white;
            pointer-events: none;
        }
        
        .navbar ul li:hover a::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: -50px;
            width: 50px;
            height: 50px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 35px -35px 0 10px white;
            pointer-events: none;
        }
        
        .main {
            position: absolute;
            width: calc(100% - 300px);
            left: 300px;
            min-height: 100vh;
            transition: 0.5s;
        }
        
        .topbar {
            width: 100%;
            padding: 0px 20px;
            height: 60px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toogle {
            cursor: pointer;
            top: 0;
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.5rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            padding: 30px;
            margin: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .form-card h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background-image: linear-gradient(to right, #FF512F 0%, #DD2476 51%, #FF512F 100%);
            margin: 10px;
            padding: 15px 45px;
            text-align: center;
            text-transform: uppercase;
            transition: 0.5s;
            background-size: 200% auto;
            color: white;
            border-radius: 9px;
            display: block;
        }
        
        .btn:hover {
            background-position: right center;
            color: #fff;
            text-decoration: none;
        }
        
        .table-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-present {
            background: #d4edda;
            color: #155724;
        }
        
        .status-absent {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-late {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-half_day {
            background: #cce5ff;
            color: #004085;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <div class="navbar">

        <div class="menu-links">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><h4>AMS</h4></span>
                        <span class="title"><h4>Attendance Management System</h4></span>
                    </a>

                </li>
                <li>
                    <a href="dashboard.php">
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="employees_page.php">
                        <span class="icon">
                            <ion-icon name="people-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Employee</span>
                    </a>
                </li>

                <li>
                    <a href="payroll.php">
                        <span class="icon">
                            <ion-icon name="cash-outline"></ion-icon></span>
                        <span class="title">Payroll</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <span class="icon"><ion-icon name="stats-chart-outline"></ion-icon></span>
                        <span class="title">Report</span>
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <div class="main">
        <div class="topbar">
            <div class="toogle">
                <ion-icon name="menu-outline"></ion-icon>
            </div>

            <div class="log_out">
                <a href="index.html">Logout</a>
            </div>
        </div>

        <div class="container">
            <?php
        if(isset($_SESSION['success_msg'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_msg'] . '</div>';
            unset($_SESSION['success_msg']);
        }
        
        if(isset($_SESSION['error_msg'])) {
            echo '<div class="alert alert-error">' . $_SESSION['error_msg'] . '</div>';
            unset($_SESSION['error_msg']);
        }
        ?>

                <div class="form-card">
                    <h2>Record Attendance</h2>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="employee_id">Select Employee *</label>
                                <select name="employee_id" id="employee_id" required>
                            <option value="">-- Choose Employee --</option>
                            <?php 
                            mysqli_data_seek($employees_result, 0);
                            while($emp = mysqli_fetch_assoc($employees_result)) { 
                            ?>
                                <option value="<?php echo $emp['id']; ?>">
                                    <?php echo $emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['department'] . ')'; ?>
                                </option>
                            <?php } ?>
                        </select>
                            </div>

                            <div class="form-group">
                                <label for="date">Date *</label>
                                <input type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="time_in">Time In *</label>
                                <input type="time" name="time_in" id="time_in" required>
                            </div>

                            <div class="form-group">
                                <label for="time_out">Time Out</label>
                                <input type="time" name="time_out" id="time_out">
                            </div>

                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select name="status" id="status" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="half_day">Half Day</option>
                            <option value="absent">Absent</option>
                        </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3" placeholder="Optional notes..."></textarea>
                        </div>

                        <button type="submit" name="submit_attendance" class="btn">Submit Attendance</button>
                    </form>
                </div>
                <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
                <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
                <script>
                    const toogle = document.querySelector('.toogle');
                    const navbar = document.querySelector('.navbar');
                    const main = document.querySelector('.main');

                    toogle.onclick = function() {
                        navbar.classList.toggle('active');
                        if (navbar.classList.contains('active')) {
                            main.style.width = 'calc(100% - 80px)';
                            main.style.left = '80px';
                        } else {
                            main.style.width = 'calc(100% - 300px)';
                            main.style.left = '300px';
                        }
                    }
                </script>

                </body>

</html>