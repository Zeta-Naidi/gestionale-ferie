<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuova Richiesta di Ferie</title>
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
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .action-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .approve-button {
            background-color: #28a745;
        }
        .reject-button {
            background-color: #dc3545;
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
        <h2>Nuova Richiesta di Ferie da Approvare</h2>
    </div>

    <div class="content">
        <p>Ciao <strong>{{ $approver->name }}</strong>,</p>
        
        <div class="alert">
            <strong>📋 Hai ricevuto una nuova richiesta di ferie da approvare!</strong>
        </div>

        <p><strong>{{ $leaveRequest->user->name }}</strong> ha inviato una richiesta di {{ $leaveRequest->type === 'vacation' ? 'ferie' : 'permesso' }} che richiede la tua approvazione.</p>

        <div class="details">
            <h3>Dettagli della richiesta:</h3>
            <ul>
                <li><strong>Richiedente:</strong> {{ $leaveRequest->user->name }}</li>
                @if($leaveRequest->user->organizationalUnit)
                <li><strong>Unità Organizzativa:</strong> {{ $leaveRequest->user->organizationalUnit->name }}</li>
                @endif
                <li><strong>Tipo:</strong> {{ $leaveRequest->type === 'vacation' ? 'Ferie' : 'Permesso' }}</li>
                <li><strong>Periodo:</strong> {{ $leaveRequest->start_date->format('d/m/Y') }} - {{ $leaveRequest->end_date->format('d/m/Y') }}</li>
                <li><strong>Giorni richiesti:</strong> {{ $leaveRequest->days_count }}</li>
                <li><strong>Data richiesta:</strong> {{ $leaveRequest->created_at->format('d/m/Y H:i') }}</li>
                @if($leaveRequest->reason)
                <li><strong>Motivo:</strong> {{ $leaveRequest->reason }}</li>
                @endif
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <p><strong>Accedi alla piattaforma per approvare o rifiutare la richiesta:</strong></p>
            <a href="{{ config('app.url') }}/dashboard" class="action-button">
                🔗 Vai alla Dashboard
            </a>
        </div>

        <p>Ricorda di gestire questa richiesta il prima possibile per non far attendere il dipendente.</p>
        
        <p>Cordiali saluti,<br>
        <strong>Sistema Gestionale Ferie</strong></p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tutti i diritti riservati.</p>
        <p>Questa è una notifica automatica, non rispondere a questa email.</p>
    </div>
</body>
</html>
