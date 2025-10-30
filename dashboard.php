<?php
require_once 'config.php';
require_login();


$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status='active'")->fetch_assoc()['count'];
$today_present = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date=CURDATE() AND status='present'")->fetch_assoc()['count'];
$pending_payroll = $conn->query("SELECT COUNT(*) as count FROM payroll WHERE status='pending'")->fetch_assoc()['count'];


$recent_attendance = $conn->query("
    SELECT a.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    ORDER BY a.date DESC, a.created_at DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Attendance Payroll System</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('img/gradient.jpg');
        }
        
        .navbar {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);*/
            position: fixed;
            width: 300px;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(100px);
            transition: 0.5s;
            overflow: hidden;
        }
        
        .navbar ul {
            position: relative;
            left: 0;
            width: 100%;
            margin: 10px 0 10px 0;
        }
        
        .navbar.active {
            width: 60px;
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
            color: white;
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
        /*---- main ----*/
        
        .main {
            position: absolute;
            width: calc(100% - 300px);
            left: 300px;
            min-height: 100vh;
            transition: 0.5s;
        }
        
        .topbar {
            width: 100%;
            height: 60px;
            padding: 0px 20px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
             margin: 30px 10px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            color: #ffffffff;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #ffffffff;
        }
        
        .employeeNum {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
        }
        
        .present {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
        }
        
        .payroll {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(216, 216, 216, 0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            margin-bottom: 20px;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            font-weight: 600;
            color: #555;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status.present {
            background: #d4edda;
            color: #155724;
        }
        
        .status.absent {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.late {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="navbar">

        <div class="menu-links">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><h4>EPS</h4></span>
                        <span class="title"><h4>Employee Payroll System</h4></span>
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
                    <a href="attendance.php">
                        <span class="icon">
                            <ion-icon name="calendar-outline"></ion-icon></span>
                        <span class="title">Attendance</span>
                    </a>
                </li>

                <li>
                    <a href="payroll.php">
                        <span class="icon"><ion-icon name="cash-outline"></ion-icon></span>
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
            <div class="welcome">
                <span>Welcome, <?php echo $_SESSION['username']; ?></span>
            </div>
            <div class="log_out">
                <a href="index.html">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card employeeNum">
                <h3>Total Employees</h3>
                <div class="number">
                    <?php echo $total_employees; ?>
                </div>
            </div>

            <div class="stat-card present">
                <h3>Present Today</h3>
                <div class="number">
                    <?php echo $today_present; ?>
                </div>
            </div>
            <div class="stat-card payroll">
                <h3>Pending Payroll</h3>
                <div class="number">
                    <?php echo $pending_payroll; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Recent Attendance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_attendance->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo $row['employee_name']; ?>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($row['date'])); ?>
                        </td>
                        <td>
                            <?php echo $row['time_in'] ? date('h:i A', strtotime($row['time_in'])) : '-'; ?>
                        </td>
                        <td>
                            <?php echo $row['time_out'] ? date('h:i A', strtotime($row['time_out'])) : '-'; ?>
                        </td>
                        <td>
                            <?php echo $row['hours_worked']; ?>
                        </td>
                        <td><span class="status <?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
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