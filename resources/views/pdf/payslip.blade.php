<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Payslip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .download-btn {
            position: absolute;
            top: 0;
            right: 0;
            padding: 8px 16px;
            background-color: #696cff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .download-btn:hover {
            background-color: #5f61e6;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .payslip-title {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .employee-info {
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .salary-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .salary-details th, .salary-details td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .salary-details th {
            background-color: #f5f5f5;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="#" class="download-btn" onclick="window.print()">Download PDF</a>
        <div class="company-name">Brothers ERP</div>
        <div class="payslip-title">Salary Payslip</div>
    </div>

    <div class="employee-info">
        <div class="info-row">
            <span class="label">Employee Name:</span>
            <span>{{ $salary->employee->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Month:</span>
            <span>{{ $month }}</span>
        </div>
        <div class="info-row">
            <span class="label">Employee ID:</span>
            <span>{{ $salary->employee->employee_id }}</span>
        </div>
        <div class="info-row">
            <span class="label">Department:</span>
            <span>{{ $salary->employee->department }}</span>
        </div>
    </div>

    <table class="salary-details">
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Basic Salary</td>
            <td>BDT {{ number_format($salary->basic_salary, 2) }}</td>
        </tr>
        <tr>
            <td>Overtime Hours</td>
            <td>{{ $salary->overtime_hours }}</td>
        </tr>
        <tr>
            <td>Commissions</td>
            <td>BDT {{ number_format($totalCommissions, 2) }}</td>
        </tr>
        <tr>
            <td>Total Earnings</td>
            <td>BDT {{ number_format($salary->total_earnings, 2) }}</td>
        </tr>
        <tr>
            <td>Deductions</td>
            <td>BDT {{ number_format($totalDeductions, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Net Salary</td>
            <td>BDT {{ number_format($salary->net_salary, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>This is a computer generated document and does not require a signature.</p>
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html> 