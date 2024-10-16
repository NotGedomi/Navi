<style>
    .content-popup {
        display: flex;
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 5;
        justify-content: center;
        align-items: center;
        background-color: #000000b3;

        .popup {
            width: 80%;
            max-width: 40rem;
            padding: 40px 50px;
            border-radius: 20px;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 20px;
            position: relative;

            >button {
                right: -1.7rem;
                align-self: end;
                position: absolute;
                top: -1.7rem;
                cursor: pointer;
                background: transparent;

                &#navi-close-redirection-popup {
                    right: -1.7rem;
                    align-self: end;
                    position: absolute;
                    top: -1.7rem;
                }
            }

            .fa-xmark {
                position: absolute;
                top: 20px;
                right: 25px;
                font-size: 30px;
                color: $negro;
                cursor: pointer;
            }

            .fa-triangle-exclamation {
                color: $rosa;
                font-size: 75px;
            }

            .redirection-disclaimer {
                color: #686868;
                text-align: center;
                font-family: Verdana;
                font-size: 14px;
                font-style: normal;
                font-weight: 400;
                line-height: 20px;
            }

            .container-buttons {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 20px;

                button {
                    color: #FFF;
                    text-align: right;
                    font-family: Verdana;
                    font-size: 14.605px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: normal;
                    padding: 9.128px 16.431px;
                    border-radius: 4.564px;
                    cursor: pointer;
                    text-wrap: wrap;
                    text-align: center;

                    &#navi-reject {
                        background: #503291;
                    }

                    &#navi-confirm {
                        background: #EB3C96;
                    }


                }
            }
        }
    }
</style>
<div id="navi-popup-redireccion" class="content-popup">
    <div class="popup">
        <button id="navi-close-redirection-popup">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">
                <path
                    d="M0.611765 26C0.203922 26 0 25.7621 0 25.2863V22.9922C0 21.7346 0.373856 20.732 1.12157 19.9843L8.20784 13.102L1.12157 6.16863C0.373856 5.42091 0 4.4183 0 3.16078V0.713725C0 0.237908 0.203922 0 0.611765 0H3.16078C4.4183 0 5.42092 0.373856 6.16863 1.12157L10.7059 5.76078C11.4196 6.47451 12.2013 6.83137 13.051 6.83137C13.9346 6.83137 14.7333 6.47451 15.4471 5.76078L19.9843 1.12157C20.732 0.373856 21.7346 0 22.9922 0H25.3882C25.7961 0 26 0.237908 26 0.713725V3.00784C26 4.26536 25.6261 5.26797 24.8784 6.01569L17.7922 12.949L24.8784 19.8314C25.6261 20.5791 26 21.5817 26 22.8392V25.2863C26 25.7621 25.7961 26 25.3882 26H22.8392C21.5817 26 20.5791 25.6261 19.8314 24.8784L15.2941 20.2392C14.5804 19.5255 13.7987 19.1686 12.949 19.1686C12.0654 19.1686 11.2667 19.5255 10.5529 20.2392L6.01569 24.8784C5.26797 25.6261 4.26536 26 3.00784 26H0.611765Z"
                    fill="#EB3C96" />
            </svg>
        </button>
        <i class="fa-light fa-triangle-exclamation"></i>
        <p class="redirection-disclaimer">En este momento usted está abandonando el sitio web de Merck. Por favor tenga
            en cuenta que el sitio web
            al que se dirige no es propiedad ni está controlado por Merck y, por tanto, no se encuentra sujeto a
            nuestras políticas. Merck no tiene injerencia por ese contenido ni tampoco por los resultados.</p>
        <div class="container-buttons">
            <button id="navi-confirm">Continuar al sitio web</button>
            <button id="navi-reject">Mantenerme en el sitio web</button>
        </div>
    </div>
</div>