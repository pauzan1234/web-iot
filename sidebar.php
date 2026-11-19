
<div class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <i class="fa-solid fa-microchip me-2"></i>
        <span>IoT Control</span>
    </div>

    <div class="sidebar-user">
        <i class="fa-solid fa-circle-user"></i>
        <div>
            <strong><?= htmlspecialchars($_SESSION['user']); ?></strong>
            <small>Administrator</small>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="active">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="index.php#kontrol-lampu">
                <i class="fa-solid fa-lightbulb"></i>
                <span>Kontrol Lampu</span>
            </a>
        </li>

        <li>
            <a href="monitoring.php">
                <i class="fa-solid fa-chart-line"></i>
                <span>Monitoring</span>
            </a>
        </li>

        <li class="menu-divider"></li>

        <li>
            <a href="index.php?logout=1" class="logout-menu">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        Sistem Monitoring dan Kontrol IoT
    </div>

</div>
