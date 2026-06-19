// Мобильная функциональность
document.addEventListener('DOMContentLoaded', function() {
    initMobileDrawer();
    initMobileToast();
    initMobileSwipe();
});

/**
 * Инициализация мобильного меню (Drawer)
 */
function initMobileDrawer() {
    const drawer = document.getElementById('mobileDrawer');
    const overlay = document.getElementById('drawerOverlay');
    const openBtn = document.getElementById('drawerToggle');
    const closeBtns = document.querySelectorAll('[data-drawer-close]');
    
    if (!drawer || !overlay || !openBtn) return;
    
    // Открытие
    openBtn.addEventListener('click', function() {
        drawer.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    });
    
    // Закрытие
    const closeDrawer = function() {
        drawer.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    };
    
    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', closeDrawer);
    });
    
    overlay.addEventListener('click', closeDrawer);
    
    // Закрытие по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDrawer();
    });
}

/**
 * Мобильные уведомления
 */
function initMobileToast() {
    // Создаем контейнер для уведомлений
    const container = document.createElement('div');
    container.id = 'mobileToast';
    container.className = 'mobile-toast';
    document.body.appendChild(container);
}

function showMobileToast(message, type = 'info', duration = 3000) {
    const toast = document.getElementById('mobileToast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = 'mobile-toast show ' + type;
    
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(function() {
        toast.classList.remove('show');
    }, duration);
}

/**
 * Мобильные свайпы
 */
function initMobileSwipe() {
    let startX = 0;
    let currentX = 0;
    let isDragging = false;
    let currentItem = null;
    
    const items = document.querySelectorAll('.swipeable-item');
    
    items.forEach(function(item) {
        item.addEventListener('touchstart', function(e) {
            startX = e.touches[0].clientX;
            currentX = startX;
            isDragging = true;
            currentItem = this;
        }, { passive: true });
        
        item.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
            const diff = startX - currentX;
            
            if (diff > 0) {
                this.style.transform = 'translateX(-' + Math.min(diff, 60) + 'px)';
            }
        }, { passive: true });
        
        item.addEventListener('touchend', function(e) {
            isDragging = false;
            const diff = startX - currentX;
            
            if (diff > 40) {
                this.classList.add('swiped');
                // Показываем кнопку удаления
                const deleteBtn = this.querySelector('.swipe-delete');
                if (deleteBtn) {
                    deleteBtn.style.display = 'block';
                }
            } else {
                this.style.transform = 'translateX(0)';
                this.classList.remove('swiped');
                const deleteBtn = this.querySelector('.swipe-delete');
                if (deleteBtn) {
                    deleteBtn.style.display = 'none';
                }
            }
        }, { passive: true });
    });
}