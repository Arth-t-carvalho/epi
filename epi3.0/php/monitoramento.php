<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Meet Layout Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #202124; font-family: 'Roboto', Arial, sans-serif; color: white; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
        header { height: 48px; display: flex; align-items: center; padding: 0 16px; font-size: 14px; background: #202124; }
        .user-info { display: flex; align-items: center; gap: 8px; }
        main { flex: 1; display: flex; padding: 8px; gap: 12px; overflow: hidden; }
        .presentation { flex: 3; background: #fff; border-radius: 8px; display: flex; flex-direction: column; color: #000; overflow: hidden; }
        .editor-header { height: 30px; background: #f3f3f3; border-bottom: 1px solid #ddd; }
        .editor-content { padding: 20px; font-family: 'Consolas', monospace; font-size: 18px; line-height: 1.5; }
        /* Grid de Pessoas e Chat Lateral */
        .sidebar { flex: 1; display: flex; flex-direction: column; gap: 12px; max-width: 400px; }
        
        .participants-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; flex: 1; }
        .participant-tile { background: #3c4043; border-radius: 8px; position: relative; min-height: 120px; display: flex; align-items: center; justify-content: center; }
        .participant-name { position: absolute; bottom: 8px; left: 8px; font-size: 10px; text-transform: uppercase; }

        .chat-panel { flex: 1; background: #fff; border-radius: 8px; color: #202124; display: flex; flex-direction: column; padding: 16px; }
        .chat-header { font-weight: 500; margin-bottom: 16px; display: flex; justify-content: space-between; }
        .chat-msg { background: #f1f3f4; padding: 8px; border-radius: 4px; font-size: 12px; margin-top: auto; }

        /* Controles Inferiores */
        footer { height: 80px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; }
        .meeting-details { font-size: 14px; }
        .controls { display: flex; gap: 12px; }
        .btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #3c4043; color: white; cursor: pointer; }
        .btn-end { background: #ea4335; width: 60px; border-radius: 24px; }
        .right-tools { display: flex; gap: 15px; }
    </style>
</head>
<body>

    <header>
        <div class="user-info">
            <span style="background:#e67e22; padding: 4px 8px; border-radius: 4px;">👤</span>
            nome do professor aqui do caba que ta logado 
        </div>
    </header>

    <main>
        <section class="presentation">
            <div class="editor-header"></div>
            <div class="editor-content">
          <h1>video ao vivo da camera
          </h1>
            </div>
        </section>

        <aside class="sidebar">
            <div class="participants-grid">
                <div class="participant-tile"><div class="participant-name">GIDEÃO DOS S...</div></div>
                <div class="participant-tile"><div class="participant-name">IAN PILOTO SA...</div></div>
                <div class="participant-tile"><div class="participant-name">RAFAEL ADRIAN...</div></div>
                <div class="participant-tile"><div style="text-align:center">Mais 14 pessoas</div></div>
            </div>

            <div class="chat-panel">
                <div class="chat-header">Mensagens na chamada <span>✕</span></div>
                <div style="font-size: 11px; color: #5f6368; text-align: center;">O chat contínuo está DESATIVADO</div>
                <div class="chat-msg">
                    <strong>ROBERTO...</strong><br>
                    %USERPROFILE%\AppData\Roaming\...
                </div>
            </div>
        </aside>
    </main>

    <footer>
        <div class="meeting-details">14:58 | jre-hhfa-oke</div>
        
        <div class="controls">
            <button class="btn">🎤</button>
            <button class="btn">🎥</button>
            <button class="btn">CC</button>
            <button class="btn">✋</button>
            <button class="btn">↑</button>
            <button class="btn">⋮</button>
            <button class="btn btn-end">📞</button>
        </div>

        <div class="right-tools">
            <span>ⓘ</span> <span>👥</span> <span>💬</span> <span>📐</span>
        </div>
    </footer>

</body>
</html>