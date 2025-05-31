import json
import datetime
from collections import defaultdict, OrderedDict

# Paths
LOGFILES = [
    "/home/t94xr/www_log/junknas-download.access.log"
]
USAGE_DATA_FILE = "/mnt/junkdrv/.usage_data"

def load_usage_data():
    """Load the existing usage data from the file."""
    try:
        with open(USAGE_DATA_FILE, 'r') as file:
            return json.load(file)
    except (FileNotFoundError, json.JSONDecodeError):
        return {'daily': {}, 'monthly': {}, 'yearly': {}}

def save_usage_data(data):
    """Save the updated usage data back to the file, sorted by date."""
    # Sort keys before saving
    data['daily'] = dict(sorted(data['daily'].items()))
    data['monthly'] = dict(sorted(data['monthly'].items()))
    data['yearly'] = dict(sorted(data['yearly'].items()))

    with open(USAGE_DATA_FILE, 'w') as file:
        json.dump(data, file, indent=4)

def process_log_file(logfile):
    """Process the given log file and return a dictionary of traffic usage."""
    buckets = defaultdict(int)

    with open(logfile, 'r') as f:
        for line in f:
            if '[' not in line:
                continue
            parts = line.split()
            if len(parts) < 10:
                continue
            date_str = line.split('[')[1].split(']')[0]
            try:
                log_time = datetime.datetime.strptime(date_str, "%d/%b/%Y:%H:%M:%S %z")
                log_time = log_time.replace(tzinfo=None)
            except ValueError:
                continue

            try:
                size = int(parts[9])
            except (IndexError, ValueError):
                size = 0

            day = log_time.date()
            buckets[day] += size

    return buckets

def update_usage_data(existing_data, new_buckets):
    """Update the usage data with the new data from the log files."""
    for day, size in new_buckets.items():
        day_str = day.strftime("%Y-%m-%d")
        month_str = day.strftime("%Y-%m")
        year_str = str(day.year)

        existing_data['daily'][day_str] = existing_data['daily'].get(day_str, 0) + size
        existing_data['monthly'][month_str] = existing_data['monthly'].get(month_str, 0) + size
        existing_data['yearly'][year_str] = existing_data['yearly'].get(year_str, 0) + size

def main():
    # Step 1: Load existing data
    existing_data = load_usage_data()

    # Step 2: Process each log file and update data
    for logfile in LOGFILES:
        new_daily = process_log_file(logfile)
        update_usage_data(existing_data, new_daily)

    # Step 3: Save the updated usage data (sorted)
    save_usage_data(existing_data)
    print("Usage data updated successfully.")

if __name__ == "__main__":
    main()
