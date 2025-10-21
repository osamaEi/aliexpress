<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Token Request - AliExpress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        pre {
            background: #1a202c;
            color: #68d391;
            padding: 20px;
            border-radius: 10px;
            max-height: 400px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="ri-tools-line me-2"></i>
                    Manual OAuth Token Request
                </h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Use this tool to manually test different OAuth parameters.</strong>
                    This helps debug issues with the token creation.
                </div>

                <form id="tokenForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">OAuth URL:</label>
                            <input type="text" class="form-control" id="tokenUrl"
                                   value="https://oauth.aliexpress.com/token">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Authorization Code:</label>
                            <input type="text" class="form-control" id="authCode"
                                   placeholder="3_517420_xxx..." required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Client ID / App Key:</label>
                            <input type="text" class="form-control" id="clientId"
                                   value="517420" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Client Secret / App Secret:</label>
                            <input type="password" class="form-control" id="clientSecret"
                                   value="y86kcMc4Yyyima1vDkUSJspmuuMc38iT" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Redirect URI:</label>
                            <input type="text" class="form-control" id="redirectUri"
                                   value="http://127.0.0.1:8000/aliexpress-oauth-callback">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Grant Type:</label>
                            <input type="text" class="form-control" id="grantType"
                                   value="authorization_code" required>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Parameter Format Test:</strong> Try these variations if it doesn't work:
                        <ul class="mb-0 mt-2">
                            <li><code>client_id</code> vs <code>app_key</code></li>
                            <li><code>client_secret</code> vs <code>app_secret</code></li>
                            <li>With or without <code>redirect_uri</code></li>
                        </ul>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="useAppKey">
                        <label class="form-check-label" for="useAppKey">
                            Use <code>app_key</code> instead of <code>client_id</code>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="ri-send-plane-line me-2"></i>
                        Send Token Request
                    </button>
                </form>

                <div id="resultSection" class="mt-4" style="display: none;">
                    <h5>Response:</h5>
                    <div id="responseStatus" class="alert"></div>
                    <pre id="responseBody"></pre>
                </div>

                <div class="mt-4">
                    <h6>Request Preview:</h6>
                    <pre id="requestPreview"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('tokenForm');
        const resultSection = document.getElementById('resultSection');
        const responseStatus = document.getElementById('responseStatus');
        const responseBody = document.getElementById('responseBody');
        const requestPreview = document.getElementById('requestPreview');

        // Update preview when fields change
        function updatePreview() {
            const useAppKey = document.getElementById('useAppKey').checked;
            const params = {
                [useAppKey ? 'app_key' : 'client_id']: document.getElementById('clientId').value,
                [useAppKey ? 'app_secret' : 'client_secret']: document.getElementById('clientSecret').value,
                'grant_type': document.getElementById('grantType').value,
                'code': document.getElementById('authCode').value || 'YOUR_CODE',
                'redirect_uri': document.getElementById('redirectUri').value,
            };

            requestPreview.textContent =
                'POST ' + document.getElementById('tokenUrl').value + '\n' +
                'Content-Type: application/x-www-form-urlencoded\n\n' +
                Object.entries(params).map(([k, v]) => `${k}=${k.includes('secret') ? '***' : v}`).join('&');
        }

        ['clientId', 'clientSecret', 'authCode', 'redirectUri', 'grantType', 'tokenUrl', 'useAppKey'].forEach(id => {
            document.getElementById(id)?.addEventListener('input', updatePreview);
            document.getElementById(id)?.addEventListener('change', updatePreview);
        });

        updatePreview();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const tokenUrl = document.getElementById('tokenUrl').value;
            const authCode = document.getElementById('authCode').value;
            const clientId = document.getElementById('clientId').value;
            const clientSecret = document.getElementById('clientSecret').value;
            const redirectUri = document.getElementById('redirectUri').value;
            const grantType = document.getElementById('grantType').value;
            const useAppKey = document.getElementById('useAppKey').checked;

            const params = new URLSearchParams({
                [useAppKey ? 'app_key' : 'client_id']: clientId,
                [useAppKey ? 'app_secret' : 'client_secret']: clientSecret,
                'grant_type': grantType,
                'code': authCode,
                'redirect_uri': redirectUri,
            });

            try {
                responseStatus.textContent = 'Sending request...';
                responseStatus.className = 'alert alert-info';
                resultSection.style.display = 'block';
                responseBody.textContent = 'Loading...';

                const response = await fetch(tokenUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: params
                });

                const data = await response.json();

                if (response.ok && !data.error_code && !data.error_msg) {
                    responseStatus.textContent = 'Success! ✅ Token received!';
                    responseStatus.className = 'alert alert-success';

                    if (data.access_token) {
                        responseBody.textContent = JSON.stringify(data, null, 2);

                        // Show success message with token
                        setTimeout(() => {
                            if (confirm('Token received! Would you like to copy the access_token?')) {
                                navigator.clipboard.writeText(data.access_token);
                                alert('Access token copied to clipboard!');
                            }
                        }, 500);
                    } else {
                        responseBody.textContent = JSON.stringify(data, null, 2);
                    }
                } else {
                    responseStatus.textContent = 'Error ❌ - Status: ' + response.status;
                    responseStatus.className = 'alert alert-danger';
                    responseBody.textContent = JSON.stringify(data, null, 2);
                }
            } catch (error) {
                responseStatus.textContent = 'Network Error ❌';
                responseStatus.className = 'alert alert-danger';
                responseBody.textContent = error.toString();
            }
        });
    </script>
</body>
</html>
