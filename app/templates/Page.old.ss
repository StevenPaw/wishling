<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$MetaTitle</title>
    <meta name="description" content="Erstelle deine eigene Wunschliste und teile sie mit Freunden und Familie">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    $ViteClient.RAW
    <link rel="stylesheet" href="$Vite("scss/main.scss")">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>🎁 Wishling</h1>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            $Layout
        </div>
    </main>
    
    <script type="module" src="$Vite("js/main.js")"></script>
</body>
</html>
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 1.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-3 {
            margin-bottom: 1.5rem;
        }
        
        .mt-3 {
            margin-top: 1.5rem;
        }
        
        .d-none {
            display: none;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>🎁 Wishling</h1>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            $Layout
        </div>
    </main>
    
    <script>
        // Basis-Funktionen für alle Seiten
        window.WishlistAPI = {
            baseURL: '/api/wishlist',
            
            async request(endpoint, options = {}) {
                const url = this.baseURL + endpoint;
                const config = {
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    },
                    ...options
                };
                
                try {
                    const response = await fetch(url, config);
                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'API Error');
                    }
                    
                    return data;
                } catch (error) {
                    console.error('API Request failed:', error);
                    throw error;
                }
            }
        };
        
        // Utility Funktionen
        window.WishlistUtils = {
            showAlert(message, type = 'success') {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} fade-in`;
                alertDiv.textContent = message;
                
                const container = document.querySelector('.container');
                container.insertBefore(alertDiv, container.firstChild);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 4000);
            },
            
            setLoading(element, loading = true) {
                if (loading) {
                    element.classList.add('loading');
                } else {
                    element.classList.remove('loading');
                }
            },
            
            copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showAlert('Link wurde in die Zwischenablage kopiert!');
                }).catch(() => {
                    this.showAlert('Kopieren fehlgeschlagen', 'error');
                });
            }
        };
    </script>
</body>
</html>
