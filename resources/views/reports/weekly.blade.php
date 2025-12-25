<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Weekly Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .muted {
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>
    <h1>Weekly Completed Tasks Report</h1>
    <div class="muted">
        Tenant: {{ $tenantName }}<br>
        Week: {{ $weekStart }} → {{ $weekEnd }}
    </div>

    <h2>Summary</h2>
    <p><strong>Completed tasks:</strong> {{ $stats['completed_tasks'] ?? 0 }}</p>

    <h2>Top Projects</h2>
    <table>
        <thead>
            <tr>
                <th>Project ID</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($stats['top_projects'] ?? []) as $row)
                <tr>
                    <td>{{ $row['project_id'] }}</td>
                    <td>{{ $row['completed'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Top Assignees</h2>
    <table>
        <thead>
            <tr>
                <th>Assignee ID</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($stats['top_assignees'] ?? []) as $row)
                <tr>
                    <td>{{ $row['assignee_id'] ?? 'Unassigned' }}</td>
                    <td>{{ $row['completed'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No data</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
