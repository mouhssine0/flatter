/**
 * Hero Section Animations
 * Adds smooth animations and interactive elements to the hero section
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize hero section animations
    initHeroAnimations();
});

/**
 * Initialize all hero section animations
 */
function initHeroAnimations() {
    // Add parallax effect to hero slides
    initParallaxEffect();
    
    // Add smooth transition between slides
    enhanceCarouselTransitions();
    
    // Initialize scroll indicator behavior
    initScrollIndicator();
}

/**
 * Add parallax effect to hero background images
 */
function initParallaxEffect() {
    const heroSlides = document.querySelectorAll('.hero-slide');
    
    window.addEventListener('scroll', function() {
        const scrollPosition = window.scrollY;
        
        if (scrollPosition < window.innerHeight) {
            heroSlides.forEach(slide => {
                // Move the background image slightly based on scroll position
                const translateY = scrollPosition * 0.3;
                slide.style.backgroundPosition = `center calc(50% + ${translateY}px)`;
            });
        }
    });
}

/**
 * Enhance carousel transitions with additional effects
 */
function enhanceCarouselTransitions() {
    const carousel = document.getElementById('heroCarousel');
    
    if (!carousel) return;
    
    // Add event listener for slide transition start
    carousel.addEventListener('slide.bs.carousel', function(e) {
        const nextSlide = e.relatedTarget;
        const heroContent = nextSlide.querySelector('.hero-content');
        
        // Reset animation by removing and re-adding the class
        if (heroContent) {
            heroContent.style.opacity = '0';
            heroContent.style.transform = 'translateY(30px)';
            
            // Force reflow
            void heroContent.offsetWidth;
            
            // Trigger animation again
            setTimeout(() => {
                heroContent.style.animation = 'fadeInUp 1s ease-out forwards';
            }, 100);
        }
    });
}

/**
 * Initialize scroll indicator behavior
 */
function initScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    
    if (!scrollIndicator) return;
    
    // Add hover effect
    scrollIndicator.addEventListener('mouseenter', function() {
        this.style.transform = 'translateX(-50%) scale(1.1)';
    });
    
    scrollIndicator.addEventListener('mouseleave', function() {
        this.style.transform = 'translateX(-50%) scale(1)';
    });
    
    // Hide scroll indicator when scrolling down
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            scrollIndicator.style.opacity = '0';
        } else {
            scrollIndicator.style.opacity = '1';
        }
    });
}