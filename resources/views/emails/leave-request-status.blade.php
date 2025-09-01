<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stato Richiesta Ferie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }
        .status-cancelled {
            color: #ffc107;
            font-weight: bold;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <h2>Stato Richiesta Ferie Aggiornato</h2>
    </div>

    <div class="content">
        <p>Ciao <strong>{{ $leaveRequest->user->name }}</strong>,</p>
        
        <p>La tua richiesta di ferie è stata <span class="status-{{ strtolower($status) }}">{{ $status }}</span> da <strong>{{ $managerName }}</strong>.</p>

        <div class="details">
            <h3>Dettagli della richiesta:</h3>
            <ul>
                <li><strong>Periodo:</strong> {{ $leaveRequest->start_date->format('d/m/Y') }} - {{ $leaveRequest->end_date->format('d/m/Y') }}</li>
                <li><strong>Giorni richiesti:</strong> {{ $leaveRequest->days_count }}</li>
                <li><strong>Tipo di permesso:</strong> {{ $leaveRequest->type }}</li>
                <li><strong>Stato attuale:</strong> {{ $status }}</li>
                <li><strong>Data richiesta:</strong> {{ $leaveRequest->created_at->format('d/m/Y H:i') }}</li>
            </ul>
        </div>

        @if($notes)
        <div class="details">
            <h3>Note aggiuntive:</h3>
            <p>{{ $notes }}</p>
        </div>
        @endif

        <p>Puoi controllare lo stato della tua richiesta accedendo alla tua area personale.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tutti i diritti riservati.</p>
    </div>
</body>
</html>
