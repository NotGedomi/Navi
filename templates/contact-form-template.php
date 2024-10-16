<style>
    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease, visibility 0.5s;

        &.active {
            opacity: 1;
            visibility: visible;
        }
    }

    .popupClinica {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 1200px;
        height: 100%;
        max-height: 50rem;
        background-color: white;
        border-radius: 2rem;
        display: flex;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.5s ease, visibility 0.5s;
        z-index: 9999;

        &.active {
            opacity: 1;
            visibility: visible;
        }

        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 10;
        }

        .big-container {
            display: flex;
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .imagenClinica {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            z-index: 1;
            transition: transform 0.5s ease;



            img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            @media (max-width: 900px) {
                display: none;
            }
        }

        .gracias,
        .formulario {
            display: flex;
            flex-direction: column;
            width: 50%;
            height: 100%;
            padding: 2rem;
            position: relative;
            right: 0;
            overflow-y: auto;
            transition: opacity 0.5s ease, visibility 0.5s;

            .titulo {
                font-size: 1.5rem;
                font-weight: 600;
                color: #503291;
                margin-bottom: 1rem;
            }

            @media (max-width: 900px) {
                width: 100% !important;
                position: absolute;
            }


        }

        .gracias {
            opacity: 0;
            visibility: hidden;
            justify-content: center;

            .agradecimiento {
                color: #686868;
                margin-bottom: 1rem;
            }

            .lista ul {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;

                li span {
                    color: #503291;
                    font-weight: 800;
                    display: block;
                    margin-bottom: 0.5rem;
                }

                li ul li {
                    color: #686868;
                    font-size: 0.75rem;
                }
            }

            #volverEncuentra {
                align-self: flex-end;
                margin-top: 1rem;
                background-color: #503291;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 0.5rem;
                cursor: pointer;
            }
        }

        .formulario {
            opacity: 1;
            visibility: visible;
            justify-content: center;
            align-items: center;

            .form {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                justify-content: center;
                align-items: center;

                p {
                    color: #686868;
                }

                form {
                    width: 100%;
                    background-color: #EEEBF4;
                    box-shadow: 0px 4px 7px 0px rgba(0, 0, 0, 0.15);
                    padding: 1rem;
                    border-radius: 1rem;

                    >div {
                        margin-bottom: 1rem;
                    }

                    label {
                        display: block;
                        margin-bottom: 0.5rem;
                        color: #525252;
                    }

                    input,
                    select {
                        width: 100%;
                        border-radius: .5rem;
                        border: 1px solid #CBCBCB;
                        padding: 1rem .5rem;
                        outline: none;

                        &.error-input {
                            border-color: red;
                        }
                    }

                    select {
                        cursor: pointer !important;
                    }

                    input[type="submit"] {
                        background-color: #EB3C96;
                        color: white;
                        font-weight: 900;
                        cursor: pointer;
                    }

                    .error-message {
                        color: red;
                        font-size: 0.8em;
                        margin-top: 5px;
                        display: none;
                    }
                }
            }

            .disclaimer {
                margin-top: 2rem;
                font-size: 0.8rem;
                color: #686868;
                width: 100%;
                text-align: start;
                justify-content: center;
                align-items: center;
                display: flex;

                p {
                    width: 90%;
                }

                @media (max-width: 900px) {
                    p {
                        font-size: 0.7rem !important;
                    }
                }
            }
        }

        &.success {
            .imagenClinica {
                transform: translateX(100%);
            }

            .gracias {
                opacity: 1;
                visibility: visible;
            }

            .formulario {
                opacity: 0;
                visibility: hidden;
            }

            @media (max-width: 900px) {
                .gracias {
                    z-index: 10;
                    opacity: 1;
                    visibility: visible;
                }

                .formulario {
                    z-index: 9;
                    opacity: 0;
                    visibility: hidden;
                }
            }
        }
    }

    .frm-message {
        margin-top: 1rem;
        padding: 0.5rem;
        border-radius: 0.25rem;

        &.success {
            background-color: #d4edda;
            color: #155724;
        }

        &.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    }

    .sede-custom {
        .contactButton {
            max-height: 0;
            overflow: hidden;
            background-color: #EB3C96;
            border-radius: .3rem;
            font-size: 1.2rem;
            width: 100%;
            color: white;
            cursor: pointer;
            transition: all .3s ease;

            &.hover {
                max-height: 15rem;
                padding: .3rem;
            }
        }
    }

    .custom-marker-container {
        width: 48px !important;
        height: 48px !important;

        .marker-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            transform-origin: bottom;
            transition: transform 0.3s ease;

            &.hover {
                transform: scale(1.2);
                z-index: 1000 !important;
            }
        }

        .custom-icon {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: all .3s ease;

            &.hover {
                scale: 1.2;
                transform-origin: bottom;
            }
        }
    }

    input,
    select {
        @media (max-width: 900px) {
            font-size: 0.8rem !important;
            padding: .7rem .7rem !important;
        }
    }

    #loader {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    /* Animación de espera al enviar form */
    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #EB3C96;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<div class="popupClinica">
    <button class="close">
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M0.611765 26C0.203922 26 0 25.7621 0 25.2863V22.9922C0 21.7346 0.373856 20.732 1.12157 19.9843L8.20784 13.102L1.12157 6.16863C0.373856 5.42091 0 4.4183 0 3.16078V0.713725C0 0.237908 0.203922 0 0.611765 0H3.16078C4.4183 0 5.42092 0.373856 6.16863 1.12157L10.7059 5.76078C11.4196 6.47451 12.2013 6.83137 13.051 6.83137C13.9346 6.83137 14.7333 6.47451 15.4471 5.76078L19.9843 1.12157C20.732 0.373856 21.7346 0 22.9922 0H25.3882C25.7961 0 26 0.237908 26 0.713725V3.00784C26 4.26536 25.6261 5.26797 24.8784 6.01569L17.7922 12.949L24.8784 19.8314C25.6261 20.5791 26 21.5817 26 22.8392V25.2863C26 25.7621 25.7961 26 25.3882 26H22.8392C21.5817 26 20.5791 25.6261 19.8314 24.8784L15.2941 20.2392C14.5804 19.5255 13.7987 19.1686 12.949 19.1686C12.0654 19.1686 11.2667 19.5255 10.5529 20.2392L6.01569 24.8784C5.26797 25.6261 4.26536 26 3.00784 26H0.611765Z"
                fill="#EB3C96" />
        </svg>
    </button>
    <div class="big-container" style="display: flex;">
        <div class="imagenClinica">
            <img src="<?php echo esc_url($sede['fondo'] ?? ''); ?>" alt="Fondo de la sede">
        </div>
        <div class="gracias">
            <span class="titulo">
                <?php echo get_field('titulo_gracias'); ?>
            </span>
            <div class="agradecimiento">
                <?php echo get_field('detalle_gracias'); ?>
            </div>
            <div class="lista">
                <ul>
                    <li>
                        <svg width="20" height="29" viewBox="0 0 20 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10.015 28.6967C9.76627 28.6967 9.51752 28.6967 9.26878 28.6967C8.95785 28.4927 8.79616 28.1738 8.6096 27.8677C7.35344 25.674 6.08484 23.4803 4.82868 21.2866C3.70932 19.348 2.58997 17.4221 1.49549 15.4707C-1.02927 10.943 -0.307909 5.77761 3.28646 2.43603C4.71674 1.10961 6.42065 0.395377 8.2738 0C9.23147 0 10.1767 0 11.1344 0C13.5721 0.484656 15.6491 1.63252 17.2286 3.63492C19.8529 6.96374 20.1265 11.6062 17.8629 15.509C15.4625 19.6796 13.0248 23.8119 10.5996 27.9697C10.4379 28.2376 10.2762 28.5054 10.015 28.6967ZM9.6419 5.89239C7.40319 5.89239 5.58735 7.70348 5.57491 9.98646C5.56248 12.3332 7.40319 14.2463 9.65433 14.2208C11.8682 14.2081 13.684 12.3332 13.6964 10.063C13.7089 7.75449 11.9055 5.90515 9.6419 5.89239Z"
                                fill="#EB3C96" />
                        </svg>
                        <span>Ubicación</span>
                        <ul>
                            <li><?php echo esc_html($sede['direccion'] ?? ''); ?></li>
                        </ul>
                    </li>
                    <li>
                        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M7.16444 0C8.15822 0.300444 8.93244 0.878222 9.55644 1.71022C10.2844 2.68089 11.0702 3.60533 11.8213 4.56444C12.7573 5.76622 12.8267 7.14133 12.0524 8.44711C11.7058 9.02489 11.3476 9.59111 11.0009 10.1689C10.6311 10.7929 10.6658 11.2204 11.1627 11.7289C12.1911 12.7689 13.2196 13.8089 14.2711 14.8258C14.7796 15.3227 15.2187 15.3573 15.8427 14.9876C16.4204 14.6409 16.9867 14.2827 17.5644 13.936C18.8124 13.1849 20.1991 13.2311 21.3431 14.1093C22.4871 14.9876 23.608 15.9004 24.7289 16.8133C25.3876 17.3449 25.7342 18.0613 26 18.8356C26 19.3787 26 19.9102 26 20.4533C24.6942 22.9493 23.0996 25.1449 20.2222 26C19.5289 26 18.8356 26 18.1422 26C9.01333 23.0302 2.95822 16.9867 0 7.85778C0 7.16444 0 6.47111 0 5.77778C0.843556 2.88889 3.03911 1.29422 5.54667 0C6.08978 0 6.62133 0 7.16444 0ZM19.1244 23.7698C20.072 23.7582 20.904 23.4462 21.5511 22.7413C22.1751 22.0596 22.776 21.3547 23.3653 20.6498C23.9893 19.8871 23.92 19.1129 23.1573 18.4773C22.152 17.6453 21.112 16.8364 20.0951 16.016C19.656 15.6578 19.1822 15.6347 18.7084 15.9236C18.1076 16.2818 17.5298 16.6516 16.9404 17.0098C15.5307 17.8533 13.9822 17.7031 12.8036 16.5707C11.6711 15.4729 10.5502 14.352 9.45245 13.2196C8.32 12.0524 8.15822 10.4693 9.00178 9.08267C9.36 8.49333 9.72978 7.904 10.088 7.31467C10.3653 6.84089 10.3653 6.36711 9.99556 5.91644C9.17511 4.89956 8.36622 3.87111 7.53422 2.85422C6.88711 2.06844 6.10133 2.01067 5.31556 2.66933C4.68 3.20089 4.04444 3.73244 3.432 4.28711C2.27644 5.32711 1.92978 6.59822 2.44978 8.05422C5.17689 15.6462 10.3538 20.8 17.9342 23.5502C18.3156 23.6889 18.7084 23.7813 19.1244 23.7698Z"
                                fill="#EB3C96" />
                        </svg>
                        <span>Teléfono(s)</span>
                        <ul>
                            <li><?php echo esc_html($sede['telefono'] ?? ''); ?></li>
                        </ul>
                    </li>
                    <li>
                        <svg width="23" height="17" viewBox="0 0 23 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M23 16.4271C22.5927 16.8668 22.0767 17 21.4793 17C14.8536 16.9867 8.22786 17 1.60213 16.9867C0.312279 16.9867 0 16.6803 0 15.4146C0 10.7782 0 6.14185 0 1.50549C0 0.346395 0.339433 0.0133229 1.53424 0.0133229C8.18713 0.0133229 14.8264 0.0133229 21.4793 0C22.0767 0 22.5927 0.119906 23 0.572884C23 5.84875 23 11.1379 23 16.4271ZM2.24026 3.53056C2.07733 3.74373 2.13164 3.93025 2.13164 4.09013C2.13164 7.3942 2.14522 10.7116 2.11806 14.0157C2.11806 14.6818 2.36246 14.8417 2.98701 14.8284C8.68949 14.815 14.392 14.815 20.0944 14.8284C20.7462 14.8284 20.9498 14.6285 20.9362 13.989C20.9091 10.6983 20.9227 7.42085 20.9227 4.13009C20.9227 3.93025 21.0584 3.63715 20.8005 3.54389C20.6104 3.47727 20.461 3.73041 20.3117 3.85031C17.5962 6.10188 14.8943 8.35345 12.1789 10.605C11.4457 11.2045 11.0112 11.1912 10.278 10.5517C8.51299 8.99295 6.73436 7.44749 4.9693 5.88871C4.05962 5.11599 3.14994 4.32994 2.24026 3.53056ZM4.05962 2.2116C4.26328 2.43809 4.35832 2.55799 4.46694 2.65125C6.62574 4.5431 8.78453 6.43495 10.9162 8.34013C11.2285 8.61991 11.3914 8.51332 11.6494 8.30016C13.6588 6.62147 15.6818 4.95611 17.6913 3.26411C18.0579 2.95768 18.4923 2.70455 18.8182 2.2116C13.8896 2.2116 9.0425 2.2116 4.05962 2.2116Z"
                                fill="#EB3C96" />
                        </svg>
                        <span>Correo electrónico</span>
                        <ul>
                            <li><?php echo esc_html($sede['correo'] ?? ''); ?></li>
                        </ul>
                    </li>
                    <li>
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_50_361)">
                                <path
                                    d="M21.9915 11.0308C21.9559 17.1868 17.0808 22.0085 10.9426 21.9907C4.82209 21.9729 -0.0529421 17.0445 0.000434148 10.924C0.0538104 4.78574 4.98222 -0.0714948 11.1027 -0.000326478C17.2054 0.0708419 22.027 4.96367 21.9915 11.0308ZM10.7824 19.7133C15.5685 19.8734 19.5361 16.1015 19.7141 11.2265C19.8742 6.44041 16.1379 2.47277 11.245 2.27706C6.42338 2.08135 2.42016 5.88885 2.27782 10.7995C2.13548 15.6033 5.90741 19.5532 10.7824 19.7133Z"
                                    fill="#EB3C96" />
                                <path
                                    d="M9.83946 8.7003C9.83946 7.98862 9.82167 7.27693 9.83946 6.56525C9.87504 5.7824 10.3554 5.24864 11.0315 5.30201C11.7788 5.35539 12.1346 5.83577 12.1524 6.54746C12.1702 7.56161 12.188 8.55796 12.1524 9.57211C12.1346 10.0703 12.2592 10.3905 12.7574 10.6041C13.3801 10.8709 13.985 11.1912 14.5722 11.5292C15.1771 11.8851 15.3728 12.4188 15.0347 13.0416C14.6967 13.6465 14.1451 13.8244 13.5046 13.522C12.5439 13.0772 11.5475 12.6501 10.6579 12.063C10.2665 11.7961 9.964 11.2268 9.85725 10.7286C9.73271 10.0881 9.83946 9.39419 9.83946 8.7003Z"
                                    fill="#EB3C96" />
                            </g>
                            <defs>
                                <clipPath id="clip0_50_361">
                                    <rect width="21.991" height="21.991" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <span>Horarios de atención</span>
                        <ul>
                            <li><?php echo esc_html($sede['horario'] ?? ''); ?></li>
                        </ul>
                    </li>
                    <li>
                        <svg width="27" height="27" viewBox="0 0 27 27" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_180_1670)">
                                <path
                                    d="M18.9381 10.8126C18.9381 11.7506 18.9487 12.6569 18.9381 13.5632C18.9276 14.1955 18.485 14.6803 17.8526 14.8173C17.2941 14.9332 16.6512 14.6382 16.4088 14.1007C16.314 13.8794 16.2718 13.6264 16.2613 13.3841C16.2191 12.7728 16.2086 12.151 16.177 11.5398C16.1665 11.2974 16.1138 11.055 16.0821 10.7915C14.4381 10.7915 12.8468 10.781 11.2449 10.8021C11.1395 10.8021 10.9498 11.0129 10.9498 11.1182C10.9287 12.699 10.9287 14.2798 10.9498 15.8501C10.9498 15.966 11.2028 16.1557 11.3503 16.1663C12.0353 16.1979 12.7203 16.1768 13.4053 16.1873C14.3011 16.1979 14.9124 16.7459 14.9018 17.5363C14.9018 18.3267 14.2906 18.8641 13.3948 18.8747C12.7625 18.8747 12.1302 18.8747 11.4241 18.8747C11.4978 19.2014 11.5294 19.4859 11.6348 19.7599C12.1828 21.1405 12.7519 22.5105 13.3105 23.8911C13.4369 24.1862 13.6477 24.3021 13.9744 24.281C14.3433 24.2599 14.7121 24.2599 15.081 24.3021C15.7765 24.3864 16.2613 24.9766 16.2508 25.6405C16.2402 26.336 15.7028 26.9262 14.9967 26.9684C10.465 27.2213 6.50252 25.8934 3.47793 22.4157C0.0528665 18.4953 -0.885072 13.9215 0.853804 9.01052C2.59268 4.11005 6.17582 1.11707 11.3187 0.200212C18.9697 -1.14873 26.136 4.33136 26.9475 12.0667C27.0423 13.0047 26.9896 13.9531 26.9369 14.9016C26.8948 15.7026 26.2203 16.2084 25.451 16.1346C24.7238 16.0609 24.2707 15.4391 24.2496 14.6592C24.2285 13.5421 24.1442 12.425 24.0915 11.3079C24.0704 10.918 23.9018 10.781 23.4908 10.7915C22.0154 10.8232 20.54 10.8021 19.0646 10.8021C19.0119 10.7915 18.9381 10.8126 18.9381 10.8126ZM8.19924 16.1873C8.19924 14.3641 8.19924 12.6042 8.19924 10.8337C8.15708 10.8232 8.10439 10.7915 8.06223 10.7915C6.52359 10.7915 4.97441 10.7915 3.43577 10.781C3.11961 10.781 3.06692 10.9707 3.02476 11.2026C2.69807 12.7096 2.69807 14.2271 3.01423 15.7447C3.07746 16.0714 3.20392 16.2189 3.56223 16.2084C4.43694 16.1873 5.31165 16.1979 6.18636 16.1979C6.86083 16.1873 7.5353 16.1873 8.19924 16.1873ZM22.869 8.08311C21.4358 5.72246 19.4967 4.1522 16.9885 3.26696C17.5049 4.88991 18.0107 6.48124 18.5166 8.08311C19.9182 8.08311 21.3304 8.08311 22.869 8.08311ZM8.55755 8.07258C9.07394 6.43909 9.56926 4.86883 10.0751 3.2775C7.57746 4.17328 5.63835 5.733 4.22617 8.07258C5.75427 8.07258 7.17699 8.07258 8.55755 8.07258ZM4.21563 18.8852C5.64889 21.2564 7.57746 22.8267 10.033 23.6803C9.59034 22.1627 9.14771 20.6557 8.69455 19.1592C8.66294 19.0433 8.50486 18.8958 8.39947 18.8958C7.03999 18.8747 5.6805 18.8852 4.21563 18.8852ZM15.6395 8.07258C15.2074 6.1967 14.5751 4.43675 13.5318 2.7611C12.499 4.46836 11.8561 6.20724 11.4451 8.07258C12.8784 8.07258 14.2379 8.07258 15.6395 8.07258Z"
                                    fill="#EB3C96" />
                                <path
                                    d="M17.8623 17.0516C17.9571 17.0832 18.1257 17.1253 18.2838 17.1991C20.6445 18.274 23.0051 19.349 25.3553 20.4134C25.6819 20.5609 25.9981 20.7085 26.0086 21.1405C26.0192 21.5726 25.7136 21.7729 25.3763 21.8888C23.6375 22.4473 22.5098 23.596 21.9407 25.3244C21.8353 25.6616 21.6246 25.9567 21.1925 25.9462C20.7815 25.9356 20.6339 25.6511 20.4969 25.3349C19.422 22.9637 18.3365 20.582 17.2616 18.2108C16.956 17.5574 17.1983 17.041 17.8623 17.0516Z"
                                    fill="#EB3C96" />
                            </g>
                            <defs>
                                <clipPath id="clip0_180_1670">
                                    <rect width="27" height="27" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <span>Sitio Web</span>
                        <ul>
                            <li><a href="<?php echo esc_url($sede['pagina_web'] ?? ''); ?>"
                                    target="_blank"><?php echo esc_html($sede['pagina_web'] ?? ''); ?></a></li>
                        </ul>
                    </li>
                    <li>
                        <button id="volverEncuentra">
                            <svg width="20" height="19" viewBox="0 0 20 19" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M3.41141 4.07759C3.52483 3.89766 3.62881 3.69879 3.76113 3.53781C7.68375 -1.24447 14.7728 -0.950907 18.289 4.13441C21.5405 8.82199 19.4327 15.6213 14.0733 17.5721C13.0809 17.932 12.0033 18.0835 10.9447 18.1972C9.9806 18.3013 9.27169 17.5911 9.24334 16.6914C9.21498 15.8107 9.84827 15.1384 10.8124 15.0816C13.0809 14.9679 14.8295 13.9925 15.9543 12.0039C17.4477 9.36177 16.5687 5.85792 14.0166 4.23858C11.3228 2.534 7.80662 3.18742 6.02963 5.74428C5.94456 5.85792 5.8784 5.98103 5.81223 6.11361C5.661 6.42611 5.76497 6.59657 6.10525 6.62498C6.31319 6.63445 6.52114 6.63445 6.71963 6.66286C7.37183 6.75756 7.85388 7.30681 7.87279 7.9697C7.89169 8.63259 7.4569 9.20078 6.8047 9.33336C6.62511 9.37124 6.42662 9.39018 6.24703 9.39018C4.85757 9.39018 3.47757 9.39965 2.08812 9.39018C0.944416 9.37124 0.443456 8.8504 0.434004 7.72348C0.434004 6.25566 0.424552 4.78783 0.443456 3.32C0.452908 2.57188 0.944416 2.02263 1.62497 1.9374C2.32442 1.85217 2.92935 2.24991 3.12785 2.95068C3.17511 3.10219 3.17511 3.26318 3.18456 3.4147C3.19401 3.58516 3.18456 3.75561 3.18456 3.92607C3.26017 3.97342 3.33579 4.03024 3.41141 4.07759Z"
                                    fill="white" />
                            </svg>
                            Volver
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="formulario">
            <div class="form">
                <p>Ingresa tus datos para realizar tu consulta:</p>
                <form id="formulario-contacto" method="post">
                    <div>
                        <label for="nombre">Nombres y Apellidos</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div>
                        <label for="correo">E-mail</label>
                        <input type="email" id="correo" name="correo" required>
                    </div>
                    <div>
                        <label for="telefono">Número telefónico</label>
                        <input type="tel" id="telefono" name="telefono" required>
                    </div>
                    <div>
                        <label for="motivo">Motivo de consulta</label>
                        <select name="motivo" id="motivo" required>
                            <?php if (have_rows('motivos_seleccion')): ?>
                                <?php while (have_rows('motivos_seleccion')):
                                    the_row(); ?>
                                    <option value="<?php echo get_sub_field('opcion') ?>"><?php echo get_sub_field('opcion') ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <input type="hidden" id="destinatario" name="destinatario"
                        value="<?php echo esc_attr($sede['correo'] ?? ''); ?>">
                    <div>
                        <input type="submit" value="Enviar">
                    </div>
                    <div id="loader" style="display: none;">
                        <div class="spinner"></div>
                        <p>Enviando formulario...</p>
                    </div>
                </form>
            </div>
            <div class="disclaimer">
                <?php echo get_field('disclaimer_formulario'); ?>
            </div>
        </div>
    </div>
</div>
<div id="overlay"></div>

<script>
    jQuery(document).ready(function ($) {
        const $popup = $('.popupClinica');
        const $overlay = $('#overlay');
        const $imagenClinica = $popup.find('.imagenClinica');
        const $form = $('#formulario-contacto');
        const $submitButton = $form.find('input[type="submit"]');
        const $gracias = $popup.find('.gracias');
        const $formulario = $popup.find('.formulario');
        const $loader = $('#loader');
        let $message;
        let currentSedeData;

        function mostrarFormulario() {
            resetearFormulario();
            $popup.addClass('active').css({ 'visibility': 'visible', 'opacity': '1', 'pointer-events': 'auto' });
            $overlay.addClass('active').css({ 'visibility': 'visible', 'opacity': '1' });
            $('body').css('overflow', 'hidden');
        }

        function ocultarFormulario() {
            $popup.removeClass('active success').css({ 'visibility': 'hidden', 'opacity': '0', 'pointer-events': 'none' });
            $overlay.removeClass('active').css({ 'visibility': 'hidden', 'opacity': '0' });
            $('body').css('overflow', '');
            setTimeout(resetearFormulario, 500);
        }

        function resetearFormulario() {
            $gracias.css({ 'opacity': '0', 'visibility': 'hidden' });
            $formulario.css({ 'opacity': '1', 'visibility': 'visible' });
            $imagenClinica.css('transform', 'translateX(0)');
            $form[0].reset();
            if ($message) {
                $message.remove();
                $message = null;
            }
            $submitButton.prop('disabled', false);
            $loader.hide();
            $('.error-message').text('').hide();
            $form.find('input, select').removeClass('error-input');
        }

        function actualizarContenidoFormulario(data) {
            currentSedeData = data;
            $imagenClinica.find('img').attr('src', data.fondo || '');
            $popup.find('.formulario .titulo').text(data.nombre || 'Sede');
            $popup.find('.gracias .titulo').text(data.nombre || 'Sede');
            actualizarListaItems('Ubicación', data.direccion);
            actualizarListaItems('Teléfono(s)', data.telefono);
            actualizarListaItems('Correo electrónico', data.correo);
            actualizarListaItems('Horarios de atención', data.horario);
            actualizarListaItems('Sitio Web', data.pagina_web, true);
            $('#destinatario').val(data.correo);
        }

        function actualizarListaItems(label, value, isLink = false) {
            const $item = $popup.find(`.lista li:contains("${label}")`);
            if ($item.length) {
                if (isLink && value) {
                    $item.find('ul li').html(`<a href="${value}" target="_blank">${value}</a>`);
                } else {
                    $item.find('ul li').text(value || 'No disponible');
                }
            } else {
                console.warn(`No se encontró el elemento para: ${label}`);
            }
        }

        function validarFormulario() {
            let isValid = true;

            // Validar nombre
            const nombre = $('#nombre').val().trim();
            if (nombre.length < 3) {
                mostrarError('nombre', 'Por favor, ingrese un nombre válido (mínimo 3 caracteres)');
                isValid = false;
            } else {
                limpiarError('nombre');
            }

            // Validar correo
            const correo = $('#correo').val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(correo)) {
                mostrarError('correo', 'Por favor, ingrese un correo electrónico válido');
                isValid = false;
            } else {
                limpiarError('correo');
            }

            // Validar teléfono
            const telefono = $('#telefono').val().trim();
            const telefonoRegex = /^\d{7,15}$/;
            if (!telefonoRegex.test(telefono)) {
                mostrarError('telefono', 'Por favor, ingrese un número de teléfono válido (solo números, 7-15 dígitos)');
                isValid = false;
            } else {
                limpiarError('telefono');
            }

            // Validar motivo
            const motivo = $('#motivo').val();
            if (!motivo) {
                mostrarError('motivo', 'Por favor, seleccione un motivo de consulta');
                isValid = false;
            } else {
                limpiarError('motivo');
            }

            return isValid;
        }

        function mostrarError(campo, mensaje) {
            $(`#${campo}-error`).text(mensaje).show();
            $(`#${campo}`).addClass('error-input');
        }

        function limpiarError(campo) {
            $(`#${campo}-error`).text('').hide();
            $(`#${campo}`).removeClass('error-input');
        }

        $(document).on('click', '.contactButton', function () {
            const buttonData = $(this).data();
            actualizarContenidoFormulario(buttonData);
            mostrarFormulario();
        });

        $popup.find('.close, #volverEncuentra').click(ocultarFormulario);

        $overlay.click(ocultarFormulario);

        $form.submit(function (e) {
            e.preventDefault();
            if (!validarFormulario()) {
                return;
            }

            const formData = {
                action: 'navi_send_contact',
                nonce: navi_form.nonce,
                nombre: $('#nombre').val(),
                correo: $('#correo').val(),
                telefono: $('#telefono').val(),
                motivo: $('#motivo').val(),
                destinatario: $('#destinatario').val()
            };

            $.ajax({
                url: navi_form.ajaxUrl,
                type: 'post',
                dataType: 'json',
                data: formData,
                beforeSend: function () {
                    $submitButton.prop('disabled', true);
                    $loader.show();
                    if ($message) {
                        $message.remove();
                    }
                }
            })
                .done(function (res) {
                    if (res.success) {
                        $form[0].reset();
                        $imagenClinica.find('img').attr('src', currentSedeData.fondo2 || currentSedeData.fondo);
                        $imagenClinica.css('transform', 'translateX(100%)');
                        $popup.addClass('success');
                        $gracias.css({ 'opacity': '1', 'visibility': 'visible' });
                        $formulario.css({ 'opacity': '0', 'visibility': 'hidden' });
                    } else {
                        mostrarMensaje('error', 'Error al enviar el mensaje. Por favor, intenta de nuevo.');
                    }
                })
                .fail(function () {
                    mostrarMensaje('error', 'Error en la conexión. Por favor, intenta de nuevo.');
                })
                .always(function () {
                    $submitButton.prop('disabled', false);
                    $loader.hide();
                });
        });
        
        function mostrarMensaje(tipo, texto) {
            if ($message) {
                $message.remove();
            }
            $message = $('<p class="frm-message ' + tipo + '"></p>').text(texto);
            $message.insertAfter($submitButton);
            setTimeout(function () {
                $message.fadeOut(function () {
                    $(this).remove();
                    $message = null;
                });
            }, 5000);
        }

        // Validación en tiempo real para el campo de teléfono
        $('#telefono').on('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Funciones de hover para el mapa
        function añadirClassHover(sedeId) {
            $('[data-sede-id="' + sedeId + '"]').addClass('hover');
        }

        function removerClassHover(sedeId) {
            $('[data-sede-id="' + sedeId + '"]').removeClass('hover');
        }

        $(document).on('mouseenter', '#navi-mapa .leaflet-marker-icon', function () {
            var sedeId = $(this).attr('data-sede-id');
            añadirClassHover(sedeId);
        }).on('mouseleave', '#navi-mapa .leaflet-marker-icon', function () {
            var sedeId = $(this).attr('data-sede-id');
            removerClassHover(sedeId);
        });

        $(document).on('mouseenter', '.sede-custom', function () {
            var sedeId = $(this).find('.contactButton').attr('data-sede-id');
            añadirClassHover(sedeId);
        }).on('mouseleave', '.sede-custom', function () {
            var sedeId = $(this).find('.contactButton').attr('data-sede-id');
            removerClassHover(sedeId);
        });

        // Inicialización
        $popup.css({ 'visibility': 'hidden', 'opacity': '0', 'pointer-events': 'none' });
        $overlay.css({ 'visibility': 'hidden', 'opacity': '0' });
    });
</script>