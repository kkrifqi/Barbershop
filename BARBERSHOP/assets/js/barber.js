// ============================================================
//  barber.js — Front-End Interactions & User Session Manager
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    initHeaderScroll();
    initMobileDrawer();
    initGalleryLightbox();
    updateNavbarSession();
});

// 1. Category Switcher Function (Guaranteed Global Access)
window.switchServiceCategory = function(cat) {
    const filterBtns = document.querySelectorAll('.tab-btn, .filter-tab-btn');
    filterBtns.forEach(btn => {
        if (btn.getAttribute('data-category') === cat) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    const dewGroup  = document.getElementById('group-dewasa');
    const anakGroup = document.getElementById('group-anak');
    const serviceCards = document.querySelectorAll('.service-card');

    if (dewGroup && anakGroup) {
        if (cat === 'all') {
            dewGroup.style.display = 'block';
            anakGroup.style.display = 'block';
        } else if (cat === 'Dewasa') {
            dewGroup.style.display = 'block';
            anakGroup.style.display = 'none';
        } else if (cat === 'Anak-anak') {
            dewGroup.style.display = 'none';
            anakGroup.style.display = 'block';
        }
    } else if (serviceCards.length) {
        serviceCards.forEach(card => {
            const cardCat = card.getAttribute('data-category');
            if (cat === 'all' || cardCat === cat) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
};

// 2. Header Sticky Effect on Scroll
function initHeaderScroll() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
}

// 3. Mobile Drawer Navigation Toggle
function initMobileDrawer() {
    const toggleBtn = document.getElementById('mobileNavToggle');
    const closeBtn  = document.getElementById('closeDrawerBtn');
    const drawer    = document.getElementById('mobileDrawer');
    const backdrop  = document.getElementById('drawerBackdrop');
    const drawerLinks = document.querySelectorAll('.mobile-nav-links a');

    if (!toggleBtn || !drawer || !backdrop) return;

    function openDrawer() {
        drawer.classList.add('open');
        backdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer.classList.remove('open');
        backdrop.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    drawerLinks.forEach(link => {
        link.addEventListener('click', closeDrawer);
    });
}

// 4. Lightbox Modal Preview for Portfolio Gallery
function initGalleryLightbox() {
    const galleryItems = document.querySelectorAll('.gallery-card, .gallery-item');
    const lightbox = document.getElementById('lightboxModal');
    const lightboxImg = document.getElementById('lightboxImage');
    const closeBtn = document.getElementById('lightboxCloseBtn');

    if (!galleryItems.length || !lightbox || !lightboxImg) return;

    galleryItems.forEach(item => {
        item.addEventListener('click', () => {
            const img = item.querySelector('img');
            if (img) {
                lightboxImg.src = img.src;
                lightbox.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });
}

// 5. Check User Login Session via API
async function updateNavbarSession() {
    try {
        const res  = await fetch('../api/session_info.php');
        if (!res.ok) return;
        const data = await res.json();

        const authBox = document.getElementById('userAuthBox');
        if (!authBox) return;

        if (data.sudahLogin) {
            authBox.innerHTML = `
                <div class="user-dropdown-wrapper" style="position: relative;">
                    <button class="btn-nav-auth" id="userMenuToggle">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <span>${data.nama}</span>
                        <ion-icon name="chevron-down-outline" style="font-size: 0.9rem;"></ion-icon>
                    </button>
                    <div class="nav-dropdown-menu" id="userDropdownMenu">
                        ${data.role === 'admin'
                            ? `<a href="../admin/admin.php" class="dropdown-item">
                                   <ion-icon name="grid-outline"></ion-icon> Admin Panel
                               </a>`
                            : ''}
                        <button class="dropdown-item dropdown-item--logout" id="btnLogoutAction">
                            <ion-icon name="log-out-outline"></ion-icon> Logout
                        </button>
                    </div>
                </div>
            `;

            const toggle = document.getElementById('userMenuToggle');
            const menu   = document.getElementById('userDropdownMenu');

            if (toggle && menu) {
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    menu.classList.toggle('show');
                });

                document.addEventListener('click', () => {
                    menu.classList.remove('show');
                });
            }

            const logoutBtn = document.getElementById('btnLogoutAction');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async () => {
                    await fetch('../api/auth.php', {
                        method : 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body   : JSON.stringify({ action: 'logout' })
                    });
                    window.location.reload();
                });
            }
        } else {
            authBox.innerHTML = `
                <a href="../login-register/login.html" class="btn-nav-auth">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    <span>Masuk</span>
                </a>
            `;
        }
    } catch (err) {
        console.warn('Gagal memuat status sesi:', err);
    }
}
