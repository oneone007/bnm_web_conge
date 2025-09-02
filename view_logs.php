<?php
// BACKUP: moved to /vente_track/view_logs.php
http_response_code(410);
echo "This page has moved to /vente_track/view_logs.php";
exit;

        $logFiles[$username] = [
            'file' => $file,
            'lines' => array_reverse($lines), // Show newest first
            'count' => count($lines),
            'total_time' => $totalTime,
            'last_modified' => filemtime($file)
        ];
    }
}

// Sort by last modified
uasort($logFiles, function($a, $b) {
    return $b['last_modified'] - $a['last_modified'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs — Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { background: #f5f7fb; color: #213547; }
        .card-quiet { background: #ffffff; border: 1px solid #e6eef7; border-radius: 10px; box-shadow: 0 6px 18px rgba(33,53,71,0.06); }
        .log-entry { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, 'Roboto Mono', monospace; font-size: 0.85rem; background: #fbfdff; padding: 8px; margin-bottom: 6px; border-radius: 6px; border: 1px solid #eef6fc; }
        .user-header { padding: 14px 18px; border-bottom: 1px solid #eef6fc; }
        .small-muted { color: #6b7280; font-size: 0.85rem; }
        .chart-card { height: 320px; }
        .filters { gap: .5rem; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Activity Logs</h3>
            <div class="small-muted">User activity overview and per-user details</div>
        </div>
        <div class="d-flex align-items-center filters">
            <span class="me-2 small-muted">Users: <strong><?php echo count($logFiles); ?></strong></span>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Back</a>
            <form method="post" action="clear_all_logs.php" class="ms-2" onsubmit="return confirm('Are you sure you want to clear all logs?');">
                <button type="submit" class="btn btn-danger btn-sm">Clear All</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card card-quiet p-3 chart-card">
                <h5 class="mb-3">Usage Share (by activity count)</h5>
                <canvas id="usageDonut" width="400" height="300"></canvas>
                <div class="mt-3 small-muted">This chart shows relative activity counts per user.</div>
            </div>
            <div class="card card-quiet p-3 mt-3">
                <h6 class="mb-2">Summary</h6>
                <?php
                $totalActivities = 0;
                $totalTimeAll = 0;
                foreach ($logFiles as $u => $d) { $totalActivities += $d['count']; $totalTimeAll += ($d['total_time'] ?? 0); }
                ?>
                <div class="d-flex justify-content-between"><div class="small-muted">Total activities</div><div><strong><?php echo $totalActivities; ?></strong></div></div>
                <div class="d-flex justify-content-between mt-1"><div class="small-muted">Total tracked time</div><div><strong><?php echo $totalTimeAll; ?>s</strong></div></div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if (empty($logFiles)): ?>
                <div class="alert alert-info">No Activity Logs Found</div>
            <?php else: ?>
                <?php foreach ($logFiles as $username => $logData): ?>
                    <div class="card card-quiet mb-3">
                        <div class="d-flex user-header align-items-center">
                            <div class="me-3" style="width:48px;height:48px;border-radius:8px;background:#eef6fc;display:flex;align-items:center;justify-content:center;font-weight:700;color:#0b69ff;"><?php echo strtoupper(substr($username,0,1)); ?></div>
                            <div class="flex-fill">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($username); ?></h5>
                                        <div class="small-muted"><?php echo $logData['count']; ?> activities • <?php echo ($logData['total_time'] ?? 0); ?>s tracked</div>
                                    </div>
                                    <div class="small-muted">Last: <?php echo date('d/m/Y H:i', $logData['last_modified']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3">
                            <div style="max-height:360px;overflow:auto;">
                                <?php foreach ($logData['lines'] as $line): ?>
                                    <?php if (!empty(trim($line))): ?>
                                        <div class="log-entry"><?php echo htmlspecialchars($line); ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<script>
// Prepare data for Chart
const usageData = <?php
    $labels = array_keys($logFiles);
    $counts = array_map(fn($d) => $d['count'], $logFiles);
    echo json_encode(['labels'=>$labels, 'counts'=>$counts]);
?>;

const ctx = document.getElementById('usageDonut');
if (ctx && usageData.labels.length) {
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: usageData.labels,
            datasets: [{
                data: usageData.counts,
                backgroundColor: ['#0b69ff','#00b894','#fc5c65','#fdcb6e','#6c5ce7','#00cec9','#fd79a8','#e17055'],
                borderWidth: 0
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } },
            responsive: true,
            maintainAspectRatio: false
        }
    });
} else if (ctx) {
    ctx.parentNode.innerHTML = '<div class="text-center small-muted">No data to display</div>';
}
</script>
</body>
</html>
