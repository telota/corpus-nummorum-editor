<div id="footer">
    <div id="footer-container">
        <!-- Left Section -->
        <div>
            <a
                href="https://www.bbaw.de"
                target="_blank"
                class="footer-link"
            >
                Berlin-Brandenburg Academy of Science and Humanities&ensp;2020&ndash;{{ date('y') }}
            </a>
        </div>

        <!-- Right Section -->
        <div>
            <a
                href="/imprint"
                target="_blank"
                class="footer-link"
            >
                Imprint
            </a>
            <a
                href="/licensing"
                class="footer-link"
            >
                Licensing
            </a>
            <a
                href="/wiki"
                class="footer-link"
            >
                Wiki
            </a>
            <a
                href="/contact"
                target="_blank"
                class="footer-link"
            >
                Contact
            </a>
        </div>
    </div>
</div>

<style>

    #footer {
        width: 100%;
        background-color: #272727;
        position: fixed;
        bottom: 0;
        z-index: 100;
        box-shadow: 2px 3px 10px rgba(0, 0, 0, 0.15);
        height: 32px;
    }

    #footer-container {
        padding: 5px 10px 5px 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 20px;
    }

    .footer-link {
        text-transform: uppercase;
        font-size: 11px;
        font-weight: 400;
        /*letter-spacing: .1rem;*/
        margin-right: 15px;
    }
</style>
