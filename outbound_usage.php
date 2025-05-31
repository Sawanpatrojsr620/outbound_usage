<?php
// Enable full error reporting for debugging HTTP 500 errors
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production

// --- CONFIGURATION OPTIONS ---
$config = [
    'dataFile' => '.usage_data', // Path to your usage data file
    'pageTitle' => 'Data Usage Dashboard', // Added page title to config

    'theme' => [
        'positiveChange' => [
            'light' => ['text' => 'rgb(22, 163, 74)', 'bg' => 'rgba(75, 175, 75, 0.15)'],
            'dark'  => ['text' => 'rgb(74, 222, 128)', 'bg' => 'rgba(75, 175, 75, 0.25)']
        ],
        'negativeChange' => [
            'light' => ['text' => 'rgb(220, 38, 38)', 'bg' => 'rgba(255, 99, 132, 0.15)'],
            'dark'  => ['text' => 'rgb(248, 113, 113)', 'bg' => 'rgba(255, 99, 132, 0.25)']
        ],
        'neutralChange' => [
            'light' => ['text' => 'rgb(107, 114, 128)', 'bg' => 'rgba(209, 213, 219, 0.3)'],
            'dark'  => ['text' => 'rgb(156, 163, 175)', 'bg' => 'rgba(75, 85, 99, 0.4)']
        ],
    ],

    'charts' => [
        'dailyUsage' => [
            'enabled' => true,
            'title' => 'Daily Usage',
            'defaultRange' => '30', // '7', '14', '30'
            'defaultType' => 'line', // 'line', 'bar'
            'colors' => [ 
                'light' => ['line' => 'rgb(75, 192, 192)', 'lineBg' => 'rgba(75, 192, 192, 0.2)', 'bar' => 'rgb(54, 162, 235)', 'barBorder' => 'rgb(54, 162, 235)'],
                'dark'  => ['line' => 'rgb(56, 189, 189)', 'lineBg' => 'rgba(56, 189, 189, 0.3)', 'bar' => 'rgb(96, 165, 250)', 'barBorder' => 'rgb(96, 165, 250)']
            ]
        ],
        'dailyUsageOlder' => [
            'enabled' => true,
            'title' => 'Daily Usage (Older)',
            'defaultRange' => '30-90', // '30-90', '90-120'
            'defaultType' => 'line',
            'colors' => [
                'light' => ['line' => 'rgb(177, 152, 200)', 'lineBg' => 'rgba(177, 152, 200, 0.2)', 'bar' => 'rgb(177, 152, 200)', 'barBorder' => 'rgb(177, 152, 200)'],
                'dark'  => ['line' => 'rgb(190, 170, 220)', 'lineBg' => 'rgba(190, 170, 220, 0.3)', 'bar' => 'rgb(190, 170, 220)', 'barBorder' => 'rgb(190, 170, 220)']
            ]
        ],
        'monthlyLast24' => [
            'enabled' => true,
            'title' => 'Monthly Usage (Last 24 Months)',
            'defaultType' => 'bar',
            'colors' => [
                'light' => ['line' => 'rgb(255, 99, 132)', 'lineBg' => 'rgba(255, 99, 132, 0.2)', 'bar' => 'rgb(255, 99, 132)', 'barBorder' => 'rgb(255, 99, 132)'],
                'dark'  => ['line' => 'rgb(251, 113, 133)', 'lineBg' => 'rgba(251, 113, 133, 0.3)', 'bar' => 'rgb(251, 113, 133)', 'barBorder' => 'rgb(251, 113, 133)']
            ]
        ],
        'monthlyYearlyComparison' => [
            'enabled' => true,
            'title' => 'Monthly Usage - Yearly Comparison',
            'defaultType' => 'line',
        ],
        'yearly' => [
            'enabled' => true,
            'title' => 'Total Usage Per Year',
            'defaultType' => 'bar',
        ],
        'yearlyDonut' => [
            'enabled' => true,
            'title' => 'Yearly Usage Distribution',
        ]
    ],
    'sections' => [
        'monthlyBreakdown' => [
            'enabled' => true,
            'title' => 'Monthly Usage Breakdown (Latest First)',
            'sparklines' => [
                'enabled' => true, 
                'responsive' => true, 
                'colors' => [ 
                    'light' => ['border' => 'rgba(0, 0, 0, 0.7)', 'bg' => 'rgba(0, 0, 0, 0.05)'],
                    'dark'  => ['border' => 'rgba(255, 255, 255, 0.8)', 'bg' => 'rgba(255, 255, 255, 0.1)']
                ]
            ]
        ]
    ],
    'chartThemeColors' => [
        'light' => [
            'axisColor' => 'rgb(107, 114, 128)', 'gridColor' => 'rgba(209, 213, 219, 0.5)', 'labelColor' => 'rgb(55, 65, 81)',
            'tooltipBg' => 'rgba(255,255,255,0.8)', 'tooltipTitle' => '#1e293b', 'tooltipBody' => '#334155', 'tooltipBorder' => '#e2e8f0',
            'donutSegmentBorder' => '#fff'
        ],
        'dark' => [
            'axisColor' => 'rgb(156, 163, 175)', 'gridColor' => 'rgba(75, 85, 99, 0.5)', 'labelColor' => 'rgb(209, 213, 219)',
            'tooltipBg' => 'rgba(30,41,59,0.8)', 'tooltipTitle' => '#e2e8f0', 'tooltipBody' => '#cbd5e1', 'tooltipBorder' => '#475569',
            'donutSegmentBorder' => '#374151' 
        ]
    ]
];

// Function to convert bytes to a human-readable format
function formatBytes($bytes, $precision = 2) {
    if ($bytes == 0) return '0 Bytes';
    $units = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// --- Data Loading and Processing ---
$allData = null;
$error_message = '';

$chart1_labels_30 = []; $chart1_data_30 = []; $chart1_labels_14 = []; $chart1_data_14 = []; $chart1_labels_7 = []; $chart1_data_7 = [];
$chart2_labels_30_90 = []; $chart2_data_30_90 = []; $chart2_labels_90_120 = []; $chart2_data_90_120 = [];
$chart3_labels = []; $chart3_data = []; $chart4_labels = []; $chart4_data = []; 
$detailed_monthly_breakdown = []; $yearly_donut_labels = []; $yearly_donut_data = [];
$yearly_comparison_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$yearly_comparison_datasets = [];

$year_color_map_php = [
    '2023' => 'rgb(255, 99, 132)', '2024' => 'rgb(54, 162, 235)', '2025' => 'rgb(255, 206, 86)',
    '2026' => 'rgb(75, 192, 192)', '2027' => 'rgb(153, 102, 255)', '2028' => 'rgb(255, 159, 64)',
    '2029' => 'rgb(128, 128, 128)', '2030' => 'rgb(75, 175, 75)', 'default' => 'rgb(201, 203, 207)'
];
$positive_change_color_php = $config['theme']['positiveChange']['light']['text']; // Default to light, JS will handle dark
$negative_change_color_php = $config['theme']['negativeChange']['light']['text'];


if (!file_exists($config['dataFile'])) {
    $error_message = "Error: Data file '{$config['dataFile']}' not found. Please ensure the file exists in the same directory as this script.";
} else {
    $jsonString = @file_get_contents($config['dataFile']);
    if ($jsonString === false) {
        $error_message = "Error: Could not read data file '{$config['dataFile']}'. Check file permissions.";
    } else {
        $allData = json_decode($jsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = "Error: Invalid JSON in '{$config['dataFile']}'. Details: " . json_last_error_msg();
            $allData = null;
        }
    }
}

if ($allData) {
    if (isset($allData['daily']) && is_array($allData['daily'])) {
        $dailyDataAll = $allData['daily']; 
        krsort($dailyDataAll); 

        if ($config['charts']['dailyUsage']['enabled']) {
            $last30Days_newestFirst = array_slice($dailyDataAll, 0, 30, true);
            if (!empty($last30Days_newestFirst)){ $chart1_labels_30 = array_reverse(array_keys($last30Days_newestFirst)); $chart1_data_30 = array_reverse(array_values($last30Days_newestFirst)); }
            $last14Days_newestFirst = array_slice($dailyDataAll, 0, 14, true);
            if (!empty($last14Days_newestFirst)){ $chart1_labels_14 = array_reverse(array_keys($last14Days_newestFirst)); $chart1_data_14 = array_reverse(array_values($last14Days_newestFirst)); }
            $last7Days_newestFirst = array_slice($dailyDataAll, 0, 7, true);
            if (!empty($last7Days_newestFirst)){ $chart1_labels_7 = array_reverse(array_keys($last7Days_newestFirst)); $chart1_data_7 = array_reverse(array_values($last7Days_newestFirst)); }
        }

        if ($config['charts']['dailyUsageOlder']['enabled']) {
            $days30to90_newestFirst = array_slice($dailyDataAll, 30, 60, true);
            if (!empty($days30to90_newestFirst)) { $chart2_labels_30_90 = array_reverse(array_keys($days30to90_newestFirst)); $chart2_data_30_90 = array_reverse(array_values($days30to90_newestFirst)); }
            $days90to120_newestFirst = array_slice($dailyDataAll, 90, 30, true);
            if (!empty($days90to120_newestFirst)) { $chart2_labels_90_120 = array_reverse(array_keys($days90to120_newestFirst)); $chart2_data_90_120 = array_reverse(array_values($days90to120_newestFirst)); }
        }
    } else {
        $error_message .= (empty($error_message) ? '' : '<br>') . "Warning: 'daily' data not found or not in expected format in JSON.";
    }

    if (isset($allData['monthly']) && is_array($allData['monthly'])) {
        $monthlyData = $allData['monthly'];
        ksort($monthlyData); 

        if ($config['charts']['monthlyLast24']['enabled']) {
            $last24Months = array_slice($monthlyData, -24, null, true);
            if(!empty($last24Months)){ $chart3_labels = array_keys($last24Months); $chart3_data = array_values($last24Months); }
        }
        
        if ($config['sections']['monthlyBreakdown']['enabled']) {
            $dailyDataForSparklines = isset($allData['daily']) && is_array($allData['daily']) ? $allData['daily'] : [];
            $temp_detailed_breakdown = [];
            $previousMonthUsage = null; 
            $isFirstMonth = true;

            foreach ($monthlyData as $monthYearKey => $totalMonthUsage) {
                $year = substr($monthYearKey, 0, 4); $month_num_str = substr($monthYearKey, 5, 2); $month_num_int = (int)$month_num_str;
                $days_in_month_data = []; $days_in_month_labels = [];
                
                if ($config['sections']['monthlyBreakdown']['sparklines']['enabled']) {
                    foreach($dailyDataForSparklines as $dailyDate => $dailyUsage) {
                        if (strpos($dailyDate, $monthYearKey) === 0) { 
                            $day = (int)substr($dailyDate, 8, 2);
                            $days_in_month_labels[] = $day; $days_in_month_data[] = $dailyUsage;
                        }
                    }
                    if (!empty($days_in_month_labels)) { array_multisort($days_in_month_labels, SORT_NUMERIC, $days_in_month_data); }
                }

                $timestamp = mktime(0, 0, 0, $month_num_int, 1, (int)$year);
                $displayYear = ($timestamp !== false) ? date('Y', $timestamp) : $year;
                $displayMonth = ($timestamp !== false) ? strtoupper(date('M', $timestamp)) : strtoupper(date('M', mktime(0,0,0,$month_num_int,1,2000)));
                
                $numericPercentageChange = null; $displayPercentage = "-"; $tooltipPercentage = "No previous month data"; $percentageClass = "percentage-neutral";

                if (!$isFirstMonth && $previousMonthUsage !== null) { 
                    if ($previousMonthUsage == 0) {
                        if ($totalMonthUsage > 0) { $displayPercentage = "New"; $numericPercentageChange = INF; $tooltipPercentage = "New usage vs zero previous"; $percentageClass = "percentage-positive"; }
                        else { $displayPercentage = "0.00%"; $numericPercentageChange = 0; $tooltipPercentage = "Same as previous (zero)"; $percentageClass = "percentage-neutral"; }
                    } else {
                        $numericPercentageChange = (($totalMonthUsage - $previousMonthUsage) / $previousMonthUsage) * 100;
                        $formattedNum = number_format(abs($numericPercentageChange), 2, '.', ',');
                        $sign = $numericPercentageChange >= 0 ? '+' : '-';
                        $displayPercentage = $sign . $formattedNum . '%';
                        if ($numericPercentageChange > 0) { $tooltipPercentage = "Above previous month"; $percentageClass = "percentage-positive"; }
                        elseif ($numericPercentageChange < 0) { $tooltipPercentage = "Below previous month"; $percentageClass = "percentage-negative"; }
                        else { $tooltipPercentage = "Same as previous month"; $percentageClass = "percentage-neutral"; }
                    }
                } else if ($isFirstMonth) { $tooltipPercentage = "First month"; }
                $isFirstMonth = false;

                $temp_detailed_breakdown[] = [
                    'id' => 'sparkline_' . $year . '_' . $month_num_str, 'displayYear' => $displayYear, 'displayMonth' => $displayMonth,
                    'formattedUsage' => formatBytes($totalMonthUsage), 'dailyLabels' => $days_in_month_labels, 'dailyData' => $days_in_month_data,
                    'numericPercentageChange' => $numericPercentageChange, 'displayPercentage' => $displayPercentage,
                    'tooltipPercentage' => $tooltipPercentage, 'percentageClass' => $percentageClass
                ];
                $previousMonthUsage = $totalMonthUsage;
            }
            $detailed_monthly_breakdown = array_reverse($temp_detailed_breakdown);
        }

        if ($config['charts']['monthlyYearlyComparison']['enabled']) {
            $tempYearlyData = [];
            foreach ($monthlyData as $monthYearKey => $usage) {
                $year = substr($monthYearKey, 0, 4); $month = (int)substr($monthYearKey, 5, 2);
                if (!isset($tempYearlyData[$year])) { $tempYearlyData[$year] = array_fill(0, 12, 0); }
                $tempYearlyData[$year][$month - 1] = $usage;
            }
            ksort($tempYearlyData);
            foreach ($tempYearlyData as $year => $monthsUsage) {
                $color = isset($year_color_map_php[$year]) ? $year_color_map_php[$year] : $year_color_map_php['default'];
                $yearly_comparison_datasets[] = [
                    'label' => $year, 'data' => $monthsUsage, 'borderColor' => $color,
                    'backgroundColor' => str_replace('rgb(', 'rgba(', $color) . ', 0.1)', 'tension' => 0.1, 'fill' => false
                ];
            }
        }
    } else {
         $error_message .= (empty($error_message) ? '' : '<br>') . "Warning: 'monthly' data not found or not in expected format.";
    }
    
    if (isset($allData['yearly']) && is_array($allData['yearly'])) {
        $yearlyData = $allData['yearly']; ksort($yearlyData);
        if(!empty($yearlyData)){
            if($config['charts']['yearly']['enabled']) { $chart4_labels = array_keys($yearlyData); $chart4_data = array_values($yearlyData); }
            if($config['charts']['yearlyDonut']['enabled']) { $yearly_donut_labels = array_keys($yearlyData); $yearly_donut_data = array_values($yearlyData); }
        }
    } else {
        $error_message .= (empty($error_message) ? '' : '<br>') . "Warning: 'yearly' data not found or not in expected format.";
    }
} else {
    if (empty($error_message)) { $error_message = "Error: Could not load or parse data from '{$config['dataFile']}'."; }
}
?>
<!DOCTYPE html>
<html lang="en" class=""> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usage Data Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937; transition: background-color 0.3s, color 0.3s; }
        .font-jetbrains-mono { font-family: 'JetBrains Mono', monospace; }
        .chart-container { background-color: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06); margin-bottom: 1.5rem; position: relative; transition: background-color 0.3s; }
        .list-container { max-height: 450px; overflow-y: auto; }
        .chart-title-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
        .chart-title-container h2 { margin-bottom: 0; color: #374151; }
        .chart-controls { display: flex; align-items: center; }
        .chart-controls select { margin-right: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; border: 1px solid #d1d5db; font-size: 0.875rem; background-color: white; color: #374151; }
        .chart-toggle-icons i { cursor: pointer; margin-left: 0.5rem; color: #6b7280; font-size: 1.1em; }
        .chart-toggle-icons i:hover { color: #374151; }
        .chart-toggle-icons i.active { color: #2563eb; font-weight: 600; }
        .no-data-message { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); color: #6b7280; font-style: italic; }
        
        .monthly-breakdown-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0.5rem; border-bottom: 1px solid #e5e7eb; }
        .monthly-breakdown-item:last-child { border-bottom: none; }
        .month-year-label { flex-basis: 20%; font-size: 1.1rem; } 
        .month-year-label .year { font-weight: 600; /* semi-bold */ color: #6b7280; /* gray-500 */ }
        .month-year-label .month { font-weight: 700; /* bold */ color: #1f2937; /* gray-800 */ margin-left: 0.25rem; }
        .sparkline-canvas-container { flex-basis: 35%; height: 40px; margin: 0 0.5rem; } 
        .percentage-change-container { flex-basis: 20%; text-align: right; padding-right: 0.5rem; }
        .percentage-change { 
            display: inline-block; font-size: 1.05rem; font-weight: 600; 
            padding: 0.2rem 0.5rem; border-radius: 0.375rem; /* rounded-md */
            min-width: 90px; text-align: center;
        }
        .percentage-positive { background-color: <?php echo $config['theme']['positiveChange']['light']['bg']; ?>; color: <?php echo $config['theme']['positiveChange']['light']['text']; ?>; }
        .percentage-negative { background-color: <?php echo $config['theme']['negativeChange']['light']['bg']; ?>; color: <?php echo $config['theme']['negativeChange']['light']['text']; ?>; }
        .percentage-neutral { background-color: <?php echo $config['theme']['neutralChange']['light']['bg']; ?>; color: <?php echo $config['theme']['neutralChange']['light']['text']; ?>; }

        .total-usage-value { flex-basis: 20%; text-align: right; font-size: 1.25rem; font-weight: 700; } 
        .theme-toggle-button { background: none; border: none; cursor: pointer; padding: 0.5rem; color: #4b5563; }

        /* Dark mode styles */
        .dark body { background-color: #1f2937; color: #d1d5db; }
        .dark .chart-container { background-color: #374151; border-color: #4b5563; }
        .dark .chart-title-container h2 { color: #f3f4f6; }
        .dark .chart-controls select { background-color: #4b5563; color: #f3f4f6; border-color: #6b7280; }
        .dark .chart-toggle-icons i { color: #9ca3af; }
        .dark .chart-toggle-icons i:hover { color: #e5e7eb; }
        .dark .chart-toggle-icons i.active { color: #60a5fa; }
        .dark .no-data-message { color: #9ca3af; }
        .dark .monthly-breakdown-item { border-bottom-color: #4b5563; }
        .dark .month-year-label .year { color: #9ca3af; }
        .dark .month-year-label .month { color: #f9fafb; }
        .dark .percentage-positive { background-color: <?php echo $config['theme']['positiveChange']['dark']['bg']; ?>; color: <?php echo $config['theme']['positiveChange']['dark']['text']; ?> !important; }
        .dark .percentage-negative { background-color: <?php echo $config['theme']['negativeChange']['dark']['bg']; ?>; color: <?php echo $config['theme']['negativeChange']['dark']['text']; ?> !important; }
        .dark .percentage-neutral { background-color: <?php echo $config['theme']['neutralChange']['dark']['bg']; ?>; color: <?php echo $config['theme']['neutralChange']['dark']['text']; ?> !important; }
        .dark .total-usage-value { color: #f9fafb; }
        .dark .theme-toggle-button { color: #d1d5db; }
        .dark footer p { color: #9ca3af; }
        .dark header h1 { color: #f9fafb; }

        /* Responsive Sparklines */
        <?php if ($config['sections']['monthlyBreakdown']['sparklines']['responsive']): ?>
        @media (max-width: 768px) { /* Tablet and smaller */
            .sparkline-canvas-container { flex-basis: 25%; height: 30px; margin: 0 0.25rem;}
            .month-year-label { flex-basis: 25%; font-size: 1rem;}
            .percentage-change-container { flex-basis: 25%;}
            .total-usage-value { flex-basis: 25%; font-size: 1.1rem;}
        }
        @media (max-width: 600px) { /* Smaller mobile */
            .sparkline-canvas-container { display: none; }
            .month-year-label { flex-basis: 30%;}
            .percentage-change-container { flex-basis: 30%; text-align: left; padding-left: 0.5rem;}
            .total-usage-value { flex-basis: 40%;}
        }
        <?php endif; ?>
    </style>
</head>
<body class="p-4 md:p-8">

    <header class="mb-8 text-center relative">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($config['pageTitle'] ?? 'Data Usage Dashboard'); ?></h1>
        <button id="theme-toggle" type="button" class="theme-toggle-button absolute top-0 right-0 mt-1 mr-1 md:mt-0 md:mr-0">
            </button>
    </header>

    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 dark:bg-red-900 dark:text-red-300 dark:border-red-700" role="alert">
            <strong class="font-bold">Error!</strong> <span class="block sm:inline"><?php echo $error_message; ?></span>
        </div>
    <?php endif; ?>

    <?php if ($config['charts']['dailyUsage']['enabled']): ?>
    <div class="mb-6 chart-container">
        <div class="chart-title-container">
            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['charts']['dailyUsage']['title']); ?></h2>
            <div class="chart-controls">
                 <select id="dailyUsageRangeSelector" onchange="updateDailyUsageChart()">
                    <option value="30" <?php echo ($config['charts']['dailyUsage']['defaultRange'] == '30' ? 'selected' : ''); ?>>Last 30 Days</option>
                    <option value="14" <?php echo ($config['charts']['dailyUsage']['defaultRange'] == '14' ? 'selected' : ''); ?>>Last 14 Days</option>
                    <option value="7" <?php echo ($config['charts']['dailyUsage']['defaultRange'] == '7' ? 'selected' : ''); ?>>Last 7 Days</option>
                </select>
                <div class="chart-toggle-icons">
                    <i class="fas fa-chart-line <?php echo ($config['charts']['dailyUsage']['defaultType'] == 'line' ? 'active' : ''); ?>" onclick="toggleChartType('chartDailyUsage', 'line')"></i>
                    <i class="fas fa-chart-bar <?php echo ($config['charts']['dailyUsage']['defaultType'] == 'bar' ? 'active' : ''); ?>" onclick="toggleChartType('chartDailyUsage', 'bar')"></i>
                </div>
            </div>
        </div>
        <canvas id="chartDailyUsage" style="max-height: 300px;"></canvas>
        <div id="chartDailyUsageNoData" class="no-data-message" style="display:none;">No data available for this chart.</div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <?php if ($config['charts']['dailyUsageOlder']['enabled']): ?>
        <div class="chart-container">
            <div class="chart-title-container">
                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['charts']['dailyUsageOlder']['title']); ?></h2>
                <div class="chart-controls">
                    <select id="dailyRangeSelectorOlder" onchange="updateDailyRangeChartOlder()">
                        <option value="30-90" <?php echo ($config['charts']['dailyUsageOlder']['defaultRange'] == '30-90' ? 'selected' : ''); ?>>30-90 Days Ago</option>
                        <option value="90-120" <?php echo ($config['charts']['dailyUsageOlder']['defaultRange'] == '90-120' ? 'selected' : ''); ?>>90-120 Days Ago</option>
                    </select>
                    <div class="chart-toggle-icons">
                        <i class="fas fa-chart-line <?php echo ($config['charts']['dailyUsageOlder']['defaultType'] == 'line' ? 'active' : ''); ?>" onclick="toggleChartType('chartDailyRangeOlder', 'line')"></i>
                        <i class="fas fa-chart-bar <?php echo ($config['charts']['dailyUsageOlder']['defaultType'] == 'bar' ? 'active' : ''); ?>" onclick="toggleChartType('chartDailyRangeOlder', 'bar')"></i>
                    </div>
                </div>
            </div>
            <canvas id="chartDailyRangeOlder"></canvas>
            <div id="chartDailyRangeOlderNoData" class="no-data-message" style="display:none;">No data available for this chart.</div>
        </div>
        <?php endif; ?>

        <?php if ($config['charts']['monthlyLast24']['enabled']): ?>
        <div class="chart-container">
            <div class="chart-title-container">
                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['charts']['monthlyLast24']['title']); ?></h2>
                 <div class="chart-toggle-icons">
                    <i class="fas fa-chart-bar <?php echo ($config['charts']['monthlyLast24']['defaultType'] == 'bar' ? 'active' : ''); ?>" onclick="toggleChartType('chartMonthlyLast24', 'bar')"></i>
                    <i class="fas fa-chart-line <?php echo ($config['charts']['monthlyLast24']['defaultType'] == 'line' ? 'active' : ''); ?>" onclick="toggleChartType('chartMonthlyLast24', 'line')"></i>
                </div>
            </div>
            <canvas id="chartMonthlyLast24"></canvas>
            <div id="chartMonthlyLast24NoData" class="no-data-message" style="display:none;">No data available for this chart.</div>
        </div>
        <?php endif; ?>
        
        <?php if ($config['charts']['monthlyYearlyComparison']['enabled']): ?>
        <div class="chart-container">
            <div class="chart-title-container">
                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['charts']['monthlyYearlyComparison']['title']); ?></h2>
                <div class="chart-toggle-icons">
                     <i class="fas fa-chart-line <?php echo ($config['charts']['monthlyYearlyComparison']['defaultType'] == 'line' ? 'active' : ''); ?>" onclick="toggleChartType('chartMonthlyYearlyComparison', 'line')"></i>
                     <i class="fas fa-chart-bar <?php echo ($config['charts']['monthlyYearlyComparison']['defaultType'] == 'bar' ? 'active' : ''); ?>" onclick="toggleChartType('chartMonthlyYearlyComparison', 'bar')"></i>
                </div>
            </div>
            <canvas id="chartMonthlyYearlyComparison"></canvas>
            <div id="chartMonthlyYearlyComparisonNoData" class="no-data-message" style="display:none;">No data available for this chart.</div>
        </div>
        <?php endif; ?>

        <?php if ($config['charts']['yearly']['enabled']): ?>
        <div class="chart-container">
            <div class="chart-title-container">
                <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['charts']['yearly']['title']); ?></h2>
                <div class="chart-toggle-icons">
                    <i class="fas fa-chart-bar <?php echo ($config['charts']['yearly']['defaultType'] == 'bar' ? 'active' : ''); ?>" onclick="toggleChartType('chartYearly', 'bar')"></i>
                    <i class="fas fa-chart-line <?php echo ($config['charts']['yearly']['defaultType'] == 'line' ? 'active' : ''); ?>" onclick="toggleChartType('chartYearly', 'line')"></i>
                </div>
            </div>
            <canvas id="chartYearly"></canvas>
            <div id="chartYearlyNoData" class="no-data-message" style="display:none;">No data available for this chart.</div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="flex flex-col lg:flex-row gap-6">
        <?php if ($config['charts']['yearlyDonut']['enabled']): ?>
        <div class="chart-container lg:w-1/2">
            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['charts']['yearlyDonut']['title']); ?></h2>
            <canvas id="chartYearlyDonut" style="max-height: 350px;"></canvas>
            <div id="chartYearlyDonutNoData" class="no-data-message" style="display:none;">No data available for this chart.</div>
        </div>
        <?php endif; ?>

        <?php if ($config['sections']['monthlyBreakdown']['enabled']): ?>
        <div class="chart-container lg:w-1/2">
            <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($config['sections']['monthlyBreakdown']['title']); ?></h2>
            <div class="list-container">
                <?php if (!empty($detailed_monthly_breakdown)): ?>
                    <?php foreach ($detailed_monthly_breakdown as $item): ?>
                        <div class="monthly-breakdown-item">
                            <div class="month-year-label font-jetbrains-mono">
                                <span class="year"><?php echo htmlspecialchars($item['displayYear']); ?></span>
                                <span class="month"><?php echo htmlspecialchars($item['displayMonth']); ?></span>
                            </div>
                            <?php if ($config['sections']['monthlyBreakdown']['sparklines']['enabled']): ?>
                            <div class="sparkline-canvas-container">
                                <canvas id="<?php echo htmlspecialchars($item['id']); ?>"></canvas>
                            </div>
                            <?php endif; ?>
                            <div class="percentage-change-container">
                                <span class="percentage-change font-jetbrains-mono <?php echo $item['percentageClass']; ?>" 
                                      title="<?php echo htmlspecialchars($item['tooltipPercentage']); ?>">
                                    <?php echo htmlspecialchars($item['displayPercentage']); ?>
                                </span>
                            </div>
                            <div class="total-usage-value font-jetbrains-mono"><?php echo htmlspecialchars($item['formattedUsage']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="monthly-breakdown-item">No monthly data to display.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="mt-12 text-center text-sm">
        <p>Usage data analysis. Current date for reference: <?php echo date('Y-m-d H:i:s'); ?></p>
    </footer>

<script>
    const appConfig = <?php echo json_encode($config); ?>;

    // --- Theme Management ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const sunIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-12.66l-.707.707M4.04 19.96l-.707.707M21 12h-1M4 12H3m16.96-4.04l-.707-.707M7.07 16.93l-.707-.707M12 5.5A6.5 6.5 0 1012 18.5 6.5 6.5 0 0012 5.5z"></path></svg>`;
    const moonIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>`;
    
    function getCurrentThemeColors() {
        const theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        let colors = { ...appConfig.chartThemeColors[theme] };
        for (const chartKey in appConfig.charts) {
            if (appConfig.charts[chartKey].colors && appConfig.charts[chartKey].colors[theme]) {
                colors[chartKey] = { ...appConfig.charts[chartKey].colors[theme] };
            }
        }
        if (appConfig.sections.monthlyBreakdown.sparklines.colors && appConfig.sections.monthlyBreakdown.sparklines.colors[theme]) {
             colors.sparkline = { ...appConfig.sections.monthlyBreakdown.sparklines.colors[theme] };
        }
        return colors;
    }

    function applyTheme(theme) {
        const themeColors = getCurrentThemeColors(); 

        Chart.defaults.color = themeColors.labelColor;
        Chart.defaults.borderColor = themeColors.gridColor;
        
        if (Chart.defaults.scale) { 
            Chart.defaults.scale.grid.color = themeColors.gridColor;
            Chart.defaults.scale.ticks.color = themeColors.labelColor;
            Chart.defaults.scale.title.color = themeColors.labelColor;
        }

        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            if(themeToggleBtn) themeToggleBtn.innerHTML = sunIcon;
        } else {
            document.documentElement.classList.remove('dark');
            if(themeToggleBtn) themeToggleBtn.innerHTML = moonIcon;
        }
    }

    function toggleTheme() {
        const isCurrentlyDark = document.documentElement.classList.contains('dark');
        const newTheme = isCurrentlyDark ? 'light' : 'dark';
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme); 
        rerenderAllCharts(); 
    }
    if (themeToggleBtn) themeToggleBtn.addEventListener('click', toggleTheme);
    
    function initializeThemeAndCharts() {
        const storedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        let initialTheme = storedTheme ? storedTheme : (systemPrefersDark ? 'dark' : 'light');
        
        applyTheme(initialTheme); 

        if (appConfig.charts.dailyUsage.enabled) {
            document.getElementById('dailyUsageRangeSelector').value = appConfig.charts.dailyUsage.defaultRange;
            updateDailyUsageChart(appConfig.charts.dailyUsage.defaultType); 
        }
        if (appConfig.charts.dailyUsageOlder.enabled) {
            document.getElementById('dailyRangeSelectorOlder').value = appConfig.charts.dailyUsageOlder.defaultRange;
            updateDailyRangeChartOlder(appConfig.charts.dailyUsageOlder.defaultType); 
        }
        
        const chart3LabelsData = <?php echo json_encode($chart3_labels); ?>;
        const chart3DataData = <?php echo json_encode($chart3_data); ?>;
        if (appConfig.charts.monthlyLast24.enabled && chart3LabelsData && chart3LabelsData.length > 0) {
            renderChart('chartMonthlyLast24', appConfig.charts.monthlyLast24.defaultType, chart3LabelsData, [{ label: 'Monthly Usage', data: chart3DataData }], 'Month (Oldest to Newest)');
        } else if(appConfig.charts.monthlyLast24.enabled) {
            const noDataEl = document.getElementById('chartMonthlyLast24NoData');
            const canvasEl = document.getElementById('chartMonthlyLast24');
            if(noDataEl) noDataEl.style.display = 'block';
            if(canvasEl) canvasEl.style.display = 'none';
        }

        const yearlyComparisonLabelsData = <?php echo json_encode($yearly_comparison_labels); ?>;
        const yearlyComparisonDatasetsData = <?php echo json_encode($yearly_comparison_datasets); ?>; 
        if (appConfig.charts.monthlyYearlyComparison.enabled && yearlyComparisonLabelsData && yearlyComparisonLabelsData.length > 0 && yearlyComparisonDatasetsData && yearlyComparisonDatasetsData.length > 0) {
            renderChart('chartMonthlyYearlyComparison', appConfig.charts.monthlyYearlyComparison.defaultType, yearlyComparisonLabelsData, yearlyComparisonDatasetsData, 'Month');
        } else if(appConfig.charts.monthlyYearlyComparison.enabled) {
             const noDataEl = document.getElementById('chartMonthlyYearlyComparisonNoData');
             const canvasEl = document.getElementById('chartMonthlyYearlyComparison');
             if(noDataEl) noDataEl.style.display = 'block';
             if(canvasEl) canvasEl.style.display = 'none';
        }

        const chart4LabelsData = <?php echo json_encode($chart4_labels); ?>; 
        const chart4DataData = <?php echo json_encode($chart4_data); ?>;   
        if (appConfig.charts.yearly.enabled && chart4LabelsData && chart4LabelsData.length > 0) {
            renderChart('chartYearly', appConfig.charts.yearly.defaultType, chart4LabelsData, [{ label: 'Total Yearly Usage', data: chart4DataData }], 'Year');
        } else if(appConfig.charts.yearly.enabled) {
            const noDataEl = document.getElementById('chartYearlyNoData');
            const canvasEl = document.getElementById('chartYearly');
            if(noDataEl) noDataEl.style.display = 'block';
            if(canvasEl) canvasEl.style.display = 'none';
        }

        const donutLabelsData = <?php echo json_encode($yearly_donut_labels); ?>;
        const donutDataData = <?php echo json_encode($yearly_donut_data); ?>;
        if (appConfig.charts.yearlyDonut.enabled) {
            renderDonutChart(donutLabelsData, donutDataData);
        }

        if (appConfig.sections.monthlyBreakdown.enabled && appConfig.sections.monthlyBreakdown.sparklines.enabled) {
            const monthlyBreakdownData = <?php echo json_encode($detailed_monthly_breakdown); ?>;
            if (monthlyBreakdownData && monthlyBreakdownData.length > 0) {
                monthlyBreakdownData.forEach(item => {
                    if (item.dailyData && item.dailyData.length > 0) {
                         renderSparklineChart(item.id, item.dailyLabels, item.dailyData);
                    } else {
                        const sparklineCanvas = document.getElementById(item.id);
                        if (sparklineCanvas) sparklineCanvas.style.display = 'none';
                    }
                });
            }
        }
    }
    
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        if (!localStorage.getItem('theme')) { 
            const newSystemTheme = event.matches ? 'dark' : 'light';
            applyTheme(newSystemTheme);
            rerenderAllCharts();
        }
    });

    const chartInstances = {}; 
    const jsYearColors = <?php echo json_encode($year_color_map_php); ?>;

    function getYearColor(year) { return jsYearColors[year] || jsYearColors['default']; }
    function getThemedYearRgbaColor(year, baseOpacity = 0.8) {
        let color = getYearColor(year); 
        let opacity = baseOpacity;
        if (document.documentElement.classList.contains('dark')) { opacity = Math.min(1, baseOpacity + 0.15); } 
        return color.replace('rgb', 'rgba').replace(')', `, ${opacity})`);
    }
    function formatBytesJs(bytes, decimals = 2) {
        if (bytes === 0 || bytes === null || typeof bytes === 'undefined') return '0 Bytes';
        const k = 1024; const dm = decimals < 0 ? 0 : decimals; const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes <= 0) return '0 Bytes'; const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    const commonTooltipCallback = {
        label: function(context) {
            let label = context.dataset.label || context.label || '';
            if (label) { label += ': '; }
            const value = context.parsed.y ?? context.parsed; 
            if (value !== null && typeof value !== 'undefined') { label += formatBytesJs(value); }
            return label;
        }
    };
    function getCommonAxisTicksConfig() { const themeColors = getCurrentThemeColors(); return { color: themeColors.labelColor }; }
    function getCommonGridConfig() { const themeColors = getCurrentThemeColors(); return { color: themeColors.gridColor }; }
    function getCommonTitleConfig() { const themeColors = getCurrentThemeColors(); return { color: themeColors.labelColor }; }

    function getChartConfig(canvasId, type, labels, datasetsConfig, titleX = '', titleY = '') {
        const themeColors = getCurrentThemeColors(); 
        let baseOptions = {
            responsive: true, maintainAspectRatio: true, 
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(value){ return formatBytesJs(value,0); }, ...getCommonAxisTicksConfig() }, grid: getCommonGridConfig(), title: { display: !!titleY, text: titleY, ...getCommonTitleConfig() } },
                x: { ticks: getCommonAxisTicksConfig(), grid: getCommonGridConfig(), title: { display: !!titleX, text: titleX, ...getCommonTitleConfig() } }
            },
            plugins: {
                tooltip: { callbacks: commonTooltipCallback, backgroundColor: themeColors.tooltipBg, titleColor: themeColors.tooltipTitle, bodyColor: themeColors.tooltipBody, borderColor: themeColors.tooltipBorder, borderWidth: 1 },
                legend: { display: datasetsConfig.length > 1 || canvasId === 'chartMonthlyYearlyComparison', labels: { color: themeColors.labelColor } }
            }
        };
        if (canvasId === 'chartDailyUsage') { baseOptions.maintainAspectRatio = false; }

        let processedDatasets = datasetsConfig.map(ds => {
            let newDs = { ...ds }; 
            let chartConfigColors = appConfig.charts[canvasId] ? appConfig.charts[canvasId].colors[document.documentElement.classList.contains('dark') ? 'dark' : 'light'] : {};
            
            if (canvasId === 'chartMonthlyYearlyComparison') { 
                newDs.type = type; newDs.fill = type === 'line' ? false : true; 
                const yearColorForDs = ds.borderColorOriginal || getYearColor(ds.label); 
                const yearRgbaBgForDs = yearColorForDs.replace('rgb(','rgba(').replace(')', document.documentElement.classList.contains('dark') ? ',0.15)' : ',0.1)');
                if (type === 'bar') { newDs.backgroundColor = yearColorForDs; newDs.borderColor = yearColorForDs; }
                else { newDs.backgroundColor = yearRgbaBgForDs; newDs.borderColor = yearColorForDs; }
                newDs.borderColorOriginal = yearColorForDs; 
            } else if (canvasId === 'chartYearly' && type === 'bar') { 
                newDs.backgroundColor = labels.map(year => getThemedYearRgbaColor(year, 0.7));
                newDs.borderColor = labels.map(year => getYearColor(year));
                newDs.borderWidth = 1; newDs.type = 'bar';
            } else if (canvasId === 'chartYearly' && type === 'line') { 
                 newDs = { ...newDs, borderColor: getYearColor(labels[0] || 'default'), backgroundColor: getThemedYearRgbaColor(labels[0] || 'default', 0.2), tension: 0.1, fill: true, pointRadius: 3, pointHoverRadius: 5, type: 'line' };
            } else { 
                 const defaultLineColor = themeColors.defaultLine || 'rgb(75,192,192)'; 
                 const defaultLineBgColor = themeColors.defaultLineBg || 'rgba(75,192,192,0.2)';
                 const defaultBarColor = themeColors.defaultBar || 'rgb(54,162,235)';

                 let colorConf = chartConfigColors[type === 'line' ? 'line' : 'bar'];
                 let bgConf = chartConfigColors[type === 'line' ? 'lineBg' : (type === 'bar' ? 'bar' : null)]; 

                 newDs.borderColor = colorConf || (type === 'line' ? defaultLineColor : defaultBarColor);
                 newDs.backgroundColor = bgConf || (type === 'line' ? defaultLineBgColor : defaultBarColor);
                 if(type === 'line') { Object.assign(newDs, { tension: 0.1, fill: true, pointRadius: 3, pointHoverRadius: 5}); }
                 else if (type === 'bar') { newDs.borderWidth = 1; }
                 newDs.type = type; 
            }
            return newDs;
        });
        return { type: type, data: { labels: labels, datasets: processedDatasets }, options: baseOptions };
    }

    function renderChart(canvasId, type, labels, datasets, titleX = '', titleY = '') {
        const ctx = document.getElementById(canvasId);
        const noDataEl = document.getElementById(canvasId + 'NoData');
        if (!ctx) return;
        if (chartInstances[canvasId]) { chartInstances[canvasId].destroy(); }

        if (!labels || labels.length === 0 || !datasets || datasets.length === 0 || datasets.every(ds => !ds.data || ds.data.length === 0)) {
            if (noDataEl) noDataEl.style.display = 'block'; if (ctx) ctx.style.display = 'none'; return;
        } else {
            if (noDataEl) noDataEl.style.display = 'none'; if (ctx) ctx.style.display = 'block'; 
        }
        
        const config = getChartConfig(canvasId, type, labels, datasets, titleX, titleY);
        chartInstances[canvasId] = new Chart(ctx, config);
        
        const iconContainer = ctx.closest('.chart-container').querySelector('.chart-toggle-icons');
        if (iconContainer) {
            Array.from(iconContainer.children).forEach(icon => icon.classList.remove('active'));
            const activeIcon = iconContainer.querySelector(type === 'line' ? '.fa-chart-line' : '.fa-chart-bar');
            if (activeIcon) activeIcon.classList.add('active');
        }
    }

    function toggleChartType(canvasId, newType) {
        const chart = chartInstances[canvasId];
        if (chart) {
            const originalDatasets = chart.config.data.datasets.map(ds => ({
                label: ds.label, data: ds.data, 
                borderColorOriginal: ds.borderColorOriginal || ds.borderColor 
            }));
            renderChart(canvasId, newType, chart.config.data.labels, originalDatasets, chart.config.options.scales.x.title.text, chart.config.options.scales.y.title.text);
        }
    }

    const chart1Data7 = { labels: <?php echo json_encode($chart1_labels_7); ?>, data: <?php echo json_encode($chart1_data_7); ?> };
    const chart1Data14 = { labels: <?php echo json_encode($chart1_labels_14); ?>, data: <?php echo json_encode($chart1_data_14); ?> };
    const chart1Data30 = { labels: <?php echo json_encode($chart1_labels_30); ?>, data: <?php echo json_encode($chart1_data_30); ?> };

    function updateDailyUsageChart(defaultType = null) {
        if (!appConfig.charts.dailyUsage.enabled) return;
        const selector = document.getElementById('dailyUsageRangeSelector');
        const selectedRange = selector.value;
        let chartDataToUse;
        if (selectedRange === '7') { chartDataToUse = chart1Data7; }
        else if (selectedRange === '14') { chartDataToUse = chart1Data14; }
        else { chartDataToUse = chart1Data30; }
        
        const currentChartInstance = chartInstances['chartDailyUsage'];
        const typeToUse = defaultType || (currentChartInstance ? currentChartInstance.config.type : appConfig.charts.dailyUsage.defaultType);
        
        if (chartDataToUse.labels && chartDataToUse.labels.length > 0) {
            renderChart('chartDailyUsage', typeToUse, chartDataToUse.labels, [{ label: 'Daily Usage', data: chartDataToUse.data }], 'Date (Oldest to Newest)');
        } else {
             document.getElementById('chartDailyUsageNoData').style.display = 'block';
             document.getElementById('chartDailyUsage').style.display = 'none';
             if (chartInstances['chartDailyUsage']) chartInstances['chartDailyUsage'].destroy();
        }
    }

    const chart2Data3090 = { labels: <?php echo json_encode($chart2_labels_30_90); ?>, data: <?php echo json_encode($chart2_data_30_90); ?> };
    const chart2Data90120 = { labels: <?php echo json_encode($chart2_labels_90_120); ?>, data: <?php echo json_encode($chart2_data_90_120); ?> };

    function updateDailyRangeChartOlder(defaultType = null) {
        if (!appConfig.charts.dailyUsageOlder.enabled) return;
        const selector = document.getElementById('dailyRangeSelectorOlder');
        const selectedRange = selector.value;
        let chartDataToUse = selectedRange === '30-90' ? chart2Data3090 : chart2Data90120;
        const currentChartInstance = chartInstances['chartDailyRangeOlder'];
        const typeToUse = defaultType || (currentChartInstance ? currentChartInstance.config.type : appConfig.charts.dailyUsageOlder.defaultType);
        
        if (chartDataToUse.labels && chartDataToUse.labels.length > 0) {
            renderChart('chartDailyRangeOlder', typeToUse, chartDataToUse.labels, [{ label: 'Daily Usage', data: chartDataToUse.data }], 'Date (Oldest to Newest)');
        } else {
             document.getElementById('chartDailyRangeOlderNoData').style.display = 'block';
             document.getElementById('chartDailyRangeOlder').style.display = 'none';
             if (chartInstances['chartDailyRangeOlder']) chartInstances['chartDailyRangeOlder'].destroy();
        }
    }

    function renderSparklineChart(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !data || data.length === 0) { if (ctx) ctx.style.display = 'none'; return; }
        if (chartInstances[canvasId]) { chartInstances[canvasId].destroy(); }
        
        const themeColors = getCurrentThemeColors();
        const sparklineConfigColors = appConfig.sections.monthlyBreakdown.sparklines.colors[document.documentElement.classList.contains('dark') ? 'dark' : 'light'];

        chartInstances[canvasId] = new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: [{ data: data, borderColor: sparklineConfigColors.border, borderWidth: 1, pointRadius: 0, fill: true, backgroundColor: sparklineConfigColors.bg, tension: 0.3 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { display: false }, y: { display: false } }, plugins: { legend: { display: false }, tooltip: { enabled: false } }, animation: false }
        });
    }
    
    function renderDonutChart(labels, data) {
        const donutCtx = document.getElementById('chartYearlyDonut');
        const donutNoDataEl = document.getElementById('chartYearlyDonutNoData');

        if (!appConfig.charts.yearlyDonut.enabled) {
            if(donutCtx) donutCtx.style.display = 'none';
            if(donutNoDataEl) donutNoDataEl.style.display = 'none'; 
            return;
        }

        if (!donutCtx) return;
        if (chartInstances['chartYearlyDonut']) chartInstances['chartYearlyDonut'].destroy();

        if (!labels || labels.length === 0 || !data || data.length === 0) {
            if (donutNoDataEl) donutNoDataEl.style.display = 'block';
            if (donutCtx) donutCtx.style.display = 'none';
            return;
        }
        if (donutNoDataEl) donutNoDataEl.style.display = 'none';
        if (donutCtx) donutCtx.style.display = 'block';
        
        const themeColors = getCurrentThemeColors();
        chartInstances['chartYearlyDonut'] = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ 
                    label: 'Yearly Usage', data: data, 
                    backgroundColor: labels.map(year => getThemedYearRgbaColor(year, 0.8)), 
                    hoverOffset: 4,
                    borderColor: themeColors.donutSegmentBorder 
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: { 
                    tooltip: { 
                        callbacks: commonTooltipCallback,
                        backgroundColor: themeColors.tooltipBg, titleColor: themeColors.tooltipTitle,
                        bodyColor: themeColors.tooltipBody, borderColor: themeColors.tooltipBorder, borderWidth: 1
                    }, 
                    legend: { position: 'top', labels: { color: themeColors.labelColor } } 
                } 
            }
        });
    }

    function rerenderAllCharts() {
        const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        applyTheme(currentTheme); 

        if (appConfig.charts.dailyUsage.enabled) { updateDailyUsageChart(); }
        if (appConfig.charts.dailyUsageOlder.enabled) { updateDailyRangeChartOlder(); }

        const chart3LabelsData = <?php echo json_encode($chart3_labels); ?>;
        const chart3DataData = <?php echo json_encode($chart3_data); ?>;
        if (appConfig.charts.monthlyLast24.enabled) {
            const chart3Instance = chartInstances['chartMonthlyLast24'];
            const type = chart3Instance ? chart3Instance.config.type : appConfig.charts.monthlyLast24.defaultType;
            if (chart3LabelsData && chart3LabelsData.length > 0) {
                renderChart('chartMonthlyLast24', type, chart3LabelsData, [{ label: 'Monthly Usage', data: chart3DataData }], 'Month (Oldest to Newest)');
            } else {
                const noDataEl = document.getElementById('chartMonthlyLast24NoData');
                const canvasEl = document.getElementById('chartMonthlyLast24');
                if(noDataEl) noDataEl.style.display = 'block'; if(canvasEl) canvasEl.style.display = 'none';
                if (chart3Instance) chart3Instance.destroy();
            }
        }
        
        const yearlyComparisonLabelsData = <?php echo json_encode($yearly_comparison_labels); ?>;
        const yearlyComparisonDatasetsData = <?php echo json_encode($yearly_comparison_datasets); ?>;
        if (appConfig.charts.monthlyYearlyComparison.enabled) {
            const yearlyCompInstance = chartInstances['chartMonthlyYearlyComparison'];
            const type = yearlyCompInstance ? yearlyCompInstance.config.type : appConfig.charts.monthlyYearlyComparison.defaultType;
            if (yearlyComparisonLabelsData && yearlyComparisonLabelsData.length > 0 && yearlyComparisonDatasetsData && yearlyComparisonDatasetsData.length > 0) {
                 const originalDatasets = yearlyCompInstance ? yearlyCompInstance.config.data.datasets.map(ds => ({
                    label: ds.label, data: ds.data, borderColorOriginal: ds.borderColorOriginal || ds.borderColor 
                })) : yearlyComparisonDatasetsData; 
                renderChart('chartMonthlyYearlyComparison', type, yearlyComparisonLabelsData, originalDatasets, 'Month');
            } else {
                const noDataEl = document.getElementById('chartMonthlyYearlyComparisonNoData');
                const canvasEl = document.getElementById('chartMonthlyYearlyComparison');
                if(noDataEl) noDataEl.style.display = 'block'; if(canvasEl) canvasEl.style.display = 'none';
                if (yearlyCompInstance) yearlyCompInstance.destroy();
            }
        }

        const chart4LabelsData = <?php echo json_encode($chart4_labels); ?>;
        const chart4DataData = <?php echo json_encode($chart4_data); ?>;
        if (appConfig.charts.yearly.enabled) {
            const yearlyInstance = chartInstances['chartYearly'];
            const type = yearlyInstance ? yearlyInstance.config.type : appConfig.charts.yearly.defaultType;
            if (chart4LabelsData && chart4LabelsData.length > 0) {
                renderChart('chartYearly', type, chart4LabelsData, [{ label: 'Total Yearly Usage', data: chart4DataData }], 'Year');
            } else {
                const noDataEl = document.getElementById('chartYearlyNoData');
                const canvasEl = document.getElementById('chartYearly');
                if(noDataEl) noDataEl.style.display = 'block'; if(canvasEl) canvasEl.style.display = 'none';
                if (yearlyInstance) yearlyInstance.destroy();
            }
        }
        
        if (appConfig.charts.yearlyDonut.enabled) {
            renderDonutChart(<?php echo json_encode($yearly_donut_labels); ?>, <?php echo json_encode($yearly_donut_data); ?>);
        }
        
        if (appConfig.sections.monthlyBreakdown.enabled && appConfig.sections.monthlyBreakdown.sparklines.enabled) {
            const monthlyBreakdownData = <?php echo json_encode($detailed_monthly_breakdown); ?>;
            if (monthlyBreakdownData && monthlyBreakdownData.length > 0) {
                monthlyBreakdownData.forEach(item => {
                    if (item.dailyData && item.dailyData.length > 0) {
                         renderSparklineChart(item.id, item.dailyLabels, item.dailyData);
                    }
                });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initializeThemeAndCharts);
</script>

</body>
</html>
