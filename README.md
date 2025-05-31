# Outbound Data Usage Dashboard

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://www.php.net/)
[![Chart.js](https://img.shields.io/badge/Chart.js-v2.9.x%2B-ff6384.svg)](https://www.chartjs.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v3.x-38B2AC.svg)](https://tailwindcss.com/)
[![Font Awesome](https://img.shields.io/badge/Font_Awesome-v6.x-528DD7.svg)](https://fontawesome.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A PHP-based web dashboard to visualize usage data generated from a JSON file. It uses Chart.js for interactive charts and Tailwind CSS for styling.

## Features

* Displays daily, monthly, and yearly usage data in various chart formats.
* Interactive charts with options to toggle between line and bar types.
* Selectable date ranges for daily usage charts.
* Detailed monthly breakdown with sparkline charts for daily usage within each month and percentage change from the previous month.
* Dark mode support with system preference detection and manual toggle.
* Highly configurable via a PHP array at the top of the script.
* Responsive design.

## File Structure

* `outbound_usage.php`: The main PHP script that renders the dashboard.
* `.usage_data`: (Default name) The JSON file containing the usage data. This file needs to be generated and updated regularly.
* `cron_usage_data.py`: (Example) A Python script to generate/update the `.usage_data` file.

## Installation

### 1. Dashboard Script

1.  Place the `outbound_usage.php` file on your web server that supports PHP (>= 7.4 recommended).
2.  Ensure the web server has read access to the usage data file (default: `.usage_data` in the same directory).

### 2. Usage Data Generation (Example using Python)

This dashboard relies on a JSON file (e.g., `.usage_data`) being present and regularly updated. Below is an example setup for a Python script (`cron_usage_data.py`) that might generate this data.

**`cron_usage_data.py` (Hypothetical Example)**

This script would be responsible for collecting your usage data (e.g., from a database, API, log files) and formatting it into the required JSON structure:

```json
{
    "daily": {
        "YYYY-MM-DD": <usage_value_bytes>,
        ...
    },
    "monthly": {
        "YYYY-MM": <total_monthly_usage_bytes>,
        ...
    },
    "yearly": {
        "YYYY": <total_yearly_usage_bytes>,
        ...
    }
}
```

**Crontab Setup**

To automate the data generation, you can set up a cron job.

1.  Make your Python script executable:
    ```bash
    chmod +x /path/to/your/cron_usage_data.py
    ```
2.  Open your crontab for editing:
    ```bash
    crontab -e
    ```
3.  Add a line to run your script at a desired interval. For example, to run it daily at 2:00 AM:
    ```cron
    0 2 * * * /usr/bin/python3 /path/to/your/cron_usage_data.py > /dev/null 2>&1
    ```
    * `0 2 * * *`: Cron schedule (minute, hour, day of month, month, day of week).
    * `/usr/bin/python3`: Path to your Python 3 interpreter (adjust if necessary).
    * `/path/to/your/cron_usage_data.py`: Absolute path to your Python script.
    * `> /dev/null 2>&1`: This redirects both standard output (stdout) and standard error (stderr) to `/dev/null`, preventing cron from sending email notifications for any script output. Remove or modify this if you want to log output or receive error emails.

    **Important:** Ensure the Python script writes the JSON output to the location specified in the `$config['dataFile']` variable within `outbound_usage.php`.

## Configuration

The `outbound_usage.php` script includes a `$config` array at the top for easy customization:

```php
<?php
// --- CONFIGURATION OPTIONS ---
$config = [
    'dataFile' => '.usage_data', // Path to your usage data file
    'pageTitle' => 'Data Usage Dashboard',

    'theme' => [
        // Colors for positive/negative percentages in monthly breakdown
        'positiveChange' => [
            'light' => ['text' => 'rgb(22, 163, 74)', 'bg' => 'rgba(75, 175, 75, 0.15)'],
            'dark'  => ['text' => 'rgb(74, 222, 128)', 'bg' => 'rgba(75, 175, 75, 0.25)']
        ],
        // ... other theme color settings ...
    ],

    'charts' => [
        'dailyUsage' => [
            'enabled' => true, // Set to false to disable this chart
            'title' => 'Daily Usage',
            'defaultRange' => '30', // '7', '14', '30'
            'defaultType' => 'line', // 'line' or 'bar'
            'colors' => [ /* ... color definitions ... */ ]
        ],
        // ... configuration for other charts ...
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
                'responsive' => true, // Toggles CSS for responsive sparklines
                'colors' => [ /* ... sparkline color definitions ... */ ]
            ]
        ]
    ],
    'chartThemeColors' => [ /* ... general chart theme colors ... */ ]
];
// ... rest of the script
```

**Key Configuration Options:**

* **`dataFile`**: Path to the JSON data file.
* **`pageTitle`**: The title displayed in the browser tab and page header.
* **`theme`**: Colors for the percentage change indicators in the monthly breakdown for light and dark modes.
* **`charts`**: An array where each key is a chart ID.
    * **`enabled`**: `true` or `false` to show/hide the chart.
    * **`title`**: Custom title for the chart.
    * **`defaultRange`**: For charts with range selectors, sets the initial view.
    * **`defaultType`**: For toggleable charts, sets the initial type (`line` or `bar`).
    * **`colors`**: Defines specific colors for line, line background, and bar elements for both light and dark themes for that chart.
* **`sections`**: Configuration for other page sections like the monthly breakdown.
    * **`monthlyBreakdown.sparklines.enabled`**: Show/hide sparklines.
    * **`monthlyBreakdown.sparklines.responsive`**: Enable/disable CSS for responsive sparkline behavior.
    * **`monthlyBreakdown.sparklines.colors`**: Colors for sparkline borders and backgrounds in light/dark modes.
* **`chartThemeColors`**: Defines the base colors for Chart.js elements like axes, grid lines, and labels for both light and dark themes.

## Example Images

*(Placeholder: You can add screenshots of your dashboard here)*

```
```

## Dependencies

* PHP (>= 7.4 recommended)
* Modern Web Browser
* Chart.js (CDN)
* Tailwind CSS (CDN)
* Font Awesome (CDN for icons)

## License

MIT License

Copyright (c) 2025 Cameron Walker - me@cameronwalker.nz

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
