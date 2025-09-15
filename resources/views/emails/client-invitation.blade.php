<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Collaboration Invitation</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .title {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .client-info {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2563eb;
        }
        .role-badge {
            display: inline-block;
            background-color: #e5e7eb;
            color: #374151;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .cta-button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <h1 class="title">You're Invited to Collaborate!</h1>
        </div>

        <div class="content">
            <p>Hello,</p>
            
            <p><strong>{{ $invitedBy->name }}</strong> has invited you to collaborate on a client project.</p>

            <div class="client-info">
                <h3 style="margin-top: 0; color: #1f2937;">Client Details</h3>
                <p><strong>Client Name:</strong> {{ $client->name }}</p>
                @if($client->contact_name)
                    <p><strong>Contact:</strong> {{ $client->contact_name }}</p>
                @endif
                @if($client->email)
                    <p><strong>Email:</strong> {{ $client->email }}</p>
                @endif
                <p><strong>Your Role:</strong> <span class="role-badge">{{ $role }}</span></p>
            </div>

            <p>As a <strong>{{ $role }}</strong>, you'll be able to collaborate on tasks and projects related to this client.</p>

            <!-- Email-safe button -->
            <table role="presentation" border="0" cellspacing="0" cellpadding="0" align="center">
            <tr>
                <td align="center" bgcolor="#2563eb" style="border-radius: 6px;">
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" 
                            xmlns:w="urn:schemas-microsoft-com:office:word" 
                            href="{{ $acceptUrl }}" 
                            style="height:50px;v-text-anchor:middle;width:200px;" 
                            arcsize="12%" 
                            stroke="f" 
                            fillcolor="#2563eb">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">
                    🔗 ACCEPT INVITATION
                    </center>
                </v:roundrect>
                <![endif]-->
                <!--[if !mso]><!-- -->
                <a href="{{ $acceptUrl }}" target="_blank" 
                    style="display:inline-block; padding:16px 32px; font-size:16px; font-weight:bold; 
                            color:#ffffff; text-decoration:none; border-radius:6px; font-family:Arial,sans-serif;">
                    🔗 ACCEPT INVITATION
                </a>
                <!--<![endif]-->
                </td>
            </tr>
            </table>

            <div class="warning">
                <strong>⚠️ Important:</strong> This invitation will expire in 7 days. If you don't have an account yet, you'll be prompted to create one when you accept the invitation.
            </div>

            <p>If you have any questions about this invitation, please contact <strong>{{ $invitedBy->name }}</strong> directly.</p>
        </div>

        <div class="footer">
            <p>This invitation was sent by {{ $invitedBy->name }} ({{ $invitedBy->email }})</p>
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>