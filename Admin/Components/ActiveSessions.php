<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'Login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Sessions - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>Admin/assets/css/AdminDashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sessions-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 30px;
            border-radius: 15px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header-content h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header-content p {
            margin: 0;
            opacity: 0.9;
        }

        .active-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px 30px;
            border-radius: 12px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .active-count-number {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .active-count-label {
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sessions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .session-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #10b981;
            transition: all 0.3s ease;
        }

        .session-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .session-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .session-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .session-info h3 {
            margin: 0 0 5px 0;
            color: #1f2937;
            font-size: 16px;
        }

        .session-id {
            color: #6b7280;
            font-size: 13px;
            font-family: 'Courier New', monospace;
        }

        .session-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #4b5563;
        }

        .detail-icon {
            width: 20px;
            color: #10b981;
        }

        .activity-pulse {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.5;
                transform: scale(1.2);
            }
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .back-link:hover {
            color: #2563eb;
        }

        .refresh-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .refresh-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .refresh-btn i {
            transition: transform 0.3s ease;
        }

        .refresh-btn.refreshing i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/Header.php'; ?>

    <div class="sessions-container">
        <a href="<?php echo BASE_URL; ?>Admin/Components/AdminDashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="page-header">
            <div class="page-header-content">
                <h1>
                    <i class="fas fa-users"></i>
                    Active Sessions
                </h1>
                <p>Real-time view of students currently logged in</p>
            </div>
            <div class="active-count">
                <div class="active-count-number" id="activeCount">0</div>
                <div class="active-count-label">Active Now</div>
            </div>
            <button class="refresh-btn" onclick="refreshSessions()">
                <i class="fas fa-sync-alt"></i>
                Refresh
            </button>
        </div>

        <div id="sessionsGrid" class="sessions-grid">
            <div class="empty-state">
                <i class="fas fa-spinner fa-spin"></i>
                <h3>Loading active sessions...</h3>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';

        function getInitials(name) {
            const parts = name.split(' ');
            if (parts.length >= 2) {
                return parts[0][0] + parts[parts.length - 1][0];
            }
            return name.substring(0, 2);
        }

        function getRandomColor(seed) {
            const colors = [
                'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
                'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
                'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
            ];
            const index = seed.charCodeAt(0) % colors.length;
            return colors[index];
        }

        function getTimeAgo(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diffMinutes = Math.floor((now - time) / 1000 / 60);

            if (diffMinutes < 1) return 'Just now';
            if (diffMinutes < 60) return `${diffMinutes}m ago`;
            const diffHours = Math.floor(diffMinutes / 60);
            if (diffHours < 24) return `${diffHours}h ago`;
            return `${Math.floor(diffHours / 24)}d ago`;
        }

        function refreshSessions() {
            const btn = document.querySelector('.refresh-btn');
            btn.classList.add('refreshing');

            fetch(BASE_URL + 'Connection/Session/TrackSession.php?action=get_active')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySessions(data.sessions);
                        document.getElementById('activeCount').textContent = data.count;
                    } else {
                        console.error('Failed to load sessions:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading sessions:', error);
                })
                .finally(() => {
                    btn.classList.remove('refreshing');
                });
        }

        function displaySessions(sessions) {
            const grid = document.getElementById('sessionsGrid');

            if (sessions.length === 0) {
                grid.innerHTML = `
          <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <h3>No Active Sessions</h3>
            <p>No students are currently logged in</p>
          </div>
        `;
                return;
            }

            grid.innerHTML = sessions.map(session => `
        <div class="session-card">
          <div class="session-header">
            <div class="session-avatar" style="background: ${getRandomColor(session.name)}">
              ${getInitials(session.name)}
            </div>
            <div class="session-info">
              <h3>${session.name}</h3>
              <div class="session-id">${session.student_id}</div>
            </div>
          </div>
          <div class="session-details">
            <div class="detail-row">
              <i class="fas fa-graduation-cap detail-icon"></i>
              <span>${session.department}</span>
            </div>
            <div class="detail-row">
              <i class="fas fa-clock detail-icon"></i>
              <span>Logged in: ${session.login_time}</span>
            </div>
            <div class="detail-row">
              <i class="fas fa-heartbeat detail-icon"></i>
              <span class="activity-pulse">
                <span class="pulse-dot"></span>
                Active ${getTimeAgo(session.last_activity)}
              </span>
            </div>
            <div class="detail-row">
              <i class="fas fa-network-wired detail-icon"></i>
              <span>${session.ip_address}</span>
            </div>
          </div>
        </div>
      `).join('');
        }

        refreshSessions();

        setInterval(refreshSessions, 30000);
    </script>
</body>

</html>