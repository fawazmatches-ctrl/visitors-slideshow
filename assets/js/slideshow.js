let slides = [];
let currentIndex = 0;
let slideDuration = 5000;
let lastLatestUpload = null;
let slideTimer = null;

const slideshowContainer = document.getElementById('slideshowContainer');
const emptyState = document.getElementById('emptyState');
const slideImage = document.getElementById('slideImage');
const captionOverlay = document.getElementById('captionOverlay');
const visitorName = document.getElementById('visitorName');
const slideMeta = document.getElementById('slideMeta');
const progressBar = document.getElementById('progressBar');

async function fetchSlides() {
    try {
        const res = await fetch('api_slides.php');
        const data = await res.json();

        if (data.status === 'ok' && data.slides.length > 0) {
            slideDuration = (data.slide_duration || 5) * 1000;
            const newestTimestamp = data.active_visitor.latest_upload;

            // If a new upload or check-in occurred, immediately start from slide 0
            if (newestTimestamp !== lastLatestUpload) {
                lastLatestUpload = newestTimestamp;
                slides = data.slides;
                currentIndex = 0;
                restartSlideshow();
            } else {
                slides = data.slides;
            }
        }
    } catch (err) {
        console.error('Failed to poll slides:', err);
    }
}

function showSlide(index) {
    if (slides.length === 0) return;

    const slide = slides[index];
    emptyState.classList.add('hidden');
    slideImage.classList.remove('hidden');
    captionOverlay.classList.remove('hidden');

    slideImage.src = slide.file_path;
    visitorName.textContent = slide.full_name;
    slideMeta.textContent = `Slide ${index + 1} of ${slides.length}`;

    // Reset and animate progress bar
    progressBar.style.transition = 'none';
    progressBar.style.width = '0%';
    void progressBar.offsetWidth; // trigger reflow
    progressBar.style.transition = `width ${slideDuration}ms linear`;
    progressBar.style.width = '100%';
}

function nextSlide() {
    if (slides.length === 0) return;
    currentIndex = (currentIndex + 1) % slides.length;
    restartSlideshow();
}

function prevSlide() {
    if (slides.length === 0) return;
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    restartSlideshow();
}

function restartSlideshow() {
    if (slideTimer) clearInterval(slideTimer);
    showSlide(currentIndex);
    slideTimer = setInterval(nextSlide, slideDuration);
}

// --- Swipe & Drag Gesture Support ---
let startX = 0;
let isDragging = false;
const SWIPE_THRESHOLD = 50; // minimum pixels to trigger slide change

// Touch swipe (smartphones, tablets, touchscreen displays)
slideshowContainer.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
}, { passive: true });

slideshowContainer.addEventListener('touchend', (e) => {
    const endX = e.changedTouches[0].clientX;
    handleSwipe(startX - endX);
}, { passive: true });

// Mouse drag swipe (desktop/laptop browsers)
slideshowContainer.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.clientX;
});

slideshowContainer.addEventListener('mouseup', (e) => {
    if (!isDragging) return;
    isDragging = false;
    handleSwipe(startX - e.clientX);
});

slideshowContainer.addEventListener('mouseleave', () => {
    isDragging = false;
});

function handleSwipe(diffX) {
    if (slides.length <= 1) return;
    if (diffX > SWIPE_THRESHOLD) {
        // Swiped left -> next slide
        nextSlide();
    } else if (diffX < -SWIPE_THRESHOLD) {
        // Swiped right -> previous slide
        prevSlide();
    }
}

// Keyboard Left/Right arrow support for TV remotes & desktops
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') nextSlide();
    if (e.key === 'ArrowLeft') prevSlide();
});

// Two-finger laptop trackpad horizontal swipe support
let wheelLocked = false;
slideshowContainer.addEventListener('wheel', (e) => {
    if (slides.length <= 1) return;

    // Only react to deliberate horizontal trackpad swipes
    if (Math.abs(e.deltaX) > Math.abs(e.deltaY) && Math.abs(e.deltaX) > 25) {
        e.preventDefault(); // Prevents browser back/forward navigation gesture
        if (wheelLocked) return;

        wheelLocked = true;
        if (e.deltaX > 0) {
            nextSlide();
        } else {
            prevSlide();
        }

        // Cooldown so one trackpad swipe advances exactly one slide
        setTimeout(() => {
            wheelLocked = false;
        }, 450);
    }
}, { passive: false });

// Initial fetch and poll every 3 seconds
fetchSlides();
setInterval(fetchSlides, 3000);