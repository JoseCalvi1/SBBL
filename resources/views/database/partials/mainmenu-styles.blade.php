<style>
    /* Estilos para centrar el menú */
    .menu-container-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        margin-top: 20px;
        padding: 10px;
        position: relative;
    }

    .menu-row {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .menu-button {
        display: inline-block;
        width: 140px;
        height: 40px;
        background: linear-gradient(135deg, #222, #444);
        clip-path: polygon(10% 0%, 100% 0%, 90% 100%, 0% 100%);
        text-align: center;
        line-height: 40px;
        color: #ffffff;
        font-weight: bold;
        font-size: 1rem;
        text-decoration: none;
        transition: transform 0.3s, background 0.3s, box-shadow 0.3s;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5);
    }

    .menu-button:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, #444, #424242);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.7);
        text-decoration: none;
    }

    /* Botón de Colección en naranja */
    .menu-button.coleccion {
        background: linear-gradient(135deg, #FFA500, #FF8C00);
    }

    .menu-button.coleccion:hover {
        background: linear-gradient(135deg, #FF8C00, #FFA500);
    }

    /* Estilos para el menú responsive */
    .menu-toggle {
        display: none;
        background: #222;
        color: #fff;
        border: none;
        padding: 10px;
        font-size: 1.2rem;
        cursor: pointer;
        width: 100%;
        text-align: left;
    }

    @media (max-width: 600px) {
        .menu-toggle {
            display: block;
        }
        .menu-row {
            display: none;
            flex-direction: column;
            align-items: center;
            width: 100%;
            background: #222;
            position: absolute;
            top: 50px;
            left: 0;
            padding: 10px 0;
        }
        .menu-container-wrapper.open .menu-row {
            display: flex;
        }
        .menu-button {
            width: 90%;
        }
    }
    </style>

    <script>
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('.menu-container-wrapper').classList.toggle('open');
    });
    </script>
