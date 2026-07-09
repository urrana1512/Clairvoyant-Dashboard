/**
 * Frontend Interactivity Script
 * 
 * Manages lightbox details view modals popups, sliding testimonial carousels, and responsive symbol toggles
 * 
 * @package Clairvoyant_Core
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
    
    // ----------------------------------------------------
    // DAILY RASHI & HOROSCOPE LIGHTBOX MODALS
    // ----------------------------------------------------
    
    // Rashi Card Click Handler (Desktop and Mobile)
    $('.cv-rashi-fal-card.cv-clickable-card, .cv-rashi-fal-mobile-card.cv-clickable-card').on('click', function(e) {
        e.preventDefault();
        
        const card = $(this);
        const modal = $('#cv-rashi-lightbox');
        
        if (!modal.length) return;

        // Retrieve properties
        const name = card.attr('data-name');
        const icon = card.attr('data-icon');
        const date = card.attr('data-date');
        const prediction = card.attr('data-prediction');
        const luckyNumber = card.attr('data-lucky-number') || '-';
        const luckyColor = card.attr('data-lucky-color');
        const rating = parseInt(card.attr('data-rating')) || 0;
        
        // Highlights life fields
        const career = card.attr('data-career') || 'Focus on your key priorities today.';
        const love = card.attr('data-love') || 'Open communication will help relationships.';
        const health = card.attr('data-health') || 'Nurture your body and stay hydrated.';
        const finance = card.attr('data-finance') || 'Avoid impulsive spends today.';

        // Populate lightbox inputs
        modal.find('#cv-lightbox-title').text(name);
        modal.find('#cv-lightbox-icon').text(icon);
        modal.find('#cv-lightbox-date').text(date);
        modal.find('#cv-lightbox-prediction').html(prediction);
        modal.find('#cv-lightbox-lucky-number').text(luckyNumber);
        
        // Lucky color
        if (luckyColor) {
            modal.find('#cv-lightbox-color-dot').css('background-color', luckyColor);
            modal.find('#cv-lightbox-color-name').text(luckyColor);
        } else {
            modal.find('#cv-lightbox-color-dot').css('background-color', 'transparent');
            modal.find('#cv-lightbox-color-name').text('-');
        }

        // Luck rating stars
        let ratingHtml = '';
        for (let i = 1; i <= 5; i++) {
            ratingHtml += i <= rating ? '★' : '☆';
        }
        modal.find('#cv-lightbox-rating-stars').html(ratingHtml);

        // Life area description highlights
        modal.find('#cv-lightbox-career').text(career);
        modal.find('#cv-lightbox-love').text(love);
        modal.find('#cv-lightbox-health').text(health);
        modal.find('#cv-lightbox-finance').text(finance);

        // Open
        modal.addClass('active');
        $('html, body').addClass('cv-modal-open');
    });

    // Detailed Horoscope Card Click Handler (Desktop and Mobile)
    $('.cv-horo-card.cv-clickable-horo-card, .cv-horo-mobile-card.cv-clickable-horo-card').on('click', function(e) {
        e.preventDefault();
        
        const card = $(this);
        const modal = $('#cv-horo-lightbox');
        
        if (!modal.length) return;

        // Retrieve properties
        const name = card.attr('data-name');
        const icon = card.attr('data-icon');
        const date = card.attr('data-date');
        const prediction = card.attr('data-prediction');
        const luckyNumber = card.attr('data-lucky-number') || '-';
        const luckyColor = card.attr('data-lucky-color');
        const rating = parseInt(card.attr('data-rating')) || 0;
        
        const career = card.attr('data-career') || 'Stay focused on tasks.';
        const love = card.attr('data-love') || 'Spend time with loved ones.';
        const health = card.attr('data-health') || 'Nourish yourself with proper sleep.';
        const money = card.attr('data-money') || 'Good day to plan investments.';

        // Populate details
        modal.find('#cv-horo-lightbox-title').text(name);
        modal.find('#cv-horo-lightbox-icon').text(icon);
        modal.find('#cv-horo-lightbox-date').text(date);
        modal.find('#cv-horo-lightbox-prediction').html(prediction);
        modal.find('#cv-horo-lightbox-lucky-number').text(luckyNumber);
        
        // Hide metadata numbers if it is weekly (where it is set to '-' or empty)
        if (luckyNumber === '-' && !luckyColor) {
            modal.find('#cv-horo-meta-row').hide();
        } else {
            modal.find('#cv-horo-meta-row').show();
            if (luckyColor) {
                modal.find('#cv-horo-lightbox-color-dot').css('background-color', luckyColor);
                modal.find('#cv-horo-lightbox-color-name').text(luckyColor);
            } else {
                modal.find('#cv-horo-lightbox-color-dot').css('background-color', 'transparent');
                modal.find('#cv-horo-lightbox-color-name').text('-');
            }
        }

        let ratingHtml = '';
        for (let i = 1; i <= 5; i++) {
            ratingHtml += i <= rating ? '★' : '☆';
        }
        modal.find('#cv-horo-lightbox-rating-stars').html(ratingHtml);

        modal.find('#cv-horo-lightbox-career').text(career);
        modal.find('#cv-horo-lightbox-love').text(love);
        modal.find('#cv-horo-lightbox-health').text(health);
        modal.find('#cv-horo-lightbox-money').text(money);

        // Open
        modal.addClass('active');
        $('html, body').addClass('cv-modal-open');
    });

    // Element Prediction Card Click Handler (Desktop and Mobile)
    $('.cv-prediction-card.cv-clickable-element-card').on('click', function(e) {
        e.preventDefault();
        
        const card = $(this);
        const modal = $('#cv-prediction-lightbox');
        
        if (!modal.length) return;

        // Retrieve properties
        const name = card.attr('data-name');
        const icon = card.attr('data-icon');
        const date = card.attr('data-date');
        const prediction = card.attr('data-prediction');
        const signs = card.attr('data-signs');

        // Populate details
        modal.find('#cv-prediction-lightbox-title').text(name);
        modal.find('#cv-prediction-lightbox-icon').text(icon);
        modal.find('#cv-prediction-lightbox-date').text(date);
        modal.find('#cv-prediction-lightbox-signs').text(signs);
        modal.find('#cv-prediction-lightbox-text').html(prediction);

        // Open
        modal.addClass('active');
        $('html, body').addClass('cv-modal-open');
    });

    // Close lightbox modal buttons action
    $('.cv-modal-lightbox, .cv-lightbox-close').on('click', function(e) {
        if ($(e.target).hasClass('cv-modal-lightbox') || $(e.target).hasClass('cv-lightbox-close') || e.target.id.indexOf('close-btn') !== -1) {
            $('.cv-modal-lightbox').removeClass('active');
            $('html, body').removeClass('cv-modal-open');
        }
    });

    // Prevent propagation inside content card
    $('.cv-lightbox-content').on('click', function(e) {
        e.stopPropagation();
    });


    // ----------------------------------------------------
    // TESTIMONIALS SLIDING CAROUSEL
    // ----------------------------------------------------
    
    const carouselSlider = $('#cv-testimonials-carousel-slider');
    if (carouselSlider.length) {
        const slides = carouselSlider.find('.cv-testimonial-slide');
        const slideCount = slides.length;
        let currentIndex = 0;
        
        const dotsContainer = $('#cv-carousel-dots-container');

        // Create dots dynamically
        if (dotsContainer.length) {
            for (let i = 0; i < slideCount; i++) {
                dotsContainer.append(`<span class="cv-carousel-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></span>`);
            }
        }

        const updateCarousel = (index) => {
            currentIndex = index;
            // Shift offset percentage
            carouselSlider.css('transform', `translateX(-${currentIndex * 100}%)`);
            
            // Sync dots
            dotsContainer.find('.cv-carousel-dot').removeClass('active');
            dotsContainer.find(`.cv-carousel-dot[data-index="${currentIndex}"]`).addClass('active');
        };

        // Next arrow trigger
        $('#cv-carousel-next-arrow').on('click', function() {
            let nextIndex = currentIndex + 1;
            if (nextIndex >= slideCount) nextIndex = 0;
            updateCarousel(nextIndex);
        });

        // Prev arrow trigger
        $('#cv-carousel-prev-arrow').on('click', function() {
            let prevIndex = currentIndex - 1;
            if (prevIndex < 0) prevIndex = slideCount - 1;
            updateCarousel(prevIndex);
        });

        // Dots click selector
        dotsContainer.on('click', '.cv-carousel-dot', function() {
            const dotIdx = parseInt($(this).attr('data-index'));
            updateCarousel(dotIdx);
        });

        // Auto play (optional, slide every 5 seconds)
        let autoSlide = setInterval(() => {
            let nextIndex = currentIndex + 1;
            if (nextIndex >= slideCount) nextIndex = 0;
            updateCarousel(nextIndex);
        }, 6000);

        // Pause auto slide on hover
        $('.cv-testimonials-carousel-wrapper').on('mouseenter', function() {
            clearInterval(autoSlide);
        }).on('mouseleave', function() {
            autoSlide = setInterval(() => {
                let nextIndex = currentIndex + 1;
                if (nextIndex >= slideCount) nextIndex = 0;
                updateCarousel(nextIndex);
            }, 6000);
        });
    }

    // ----------------------------------------------------
    // HOROSCOPE TABS INTERACTIVITY
    // ----------------------------------------------------
    $('.cv-tab-button').on('click', function(e) {
        e.preventDefault();
        const btn = $(this);
        const tab = btn.attr('data-tab');
        const wrapper = btn.closest('.cv-horoscope-tabs-wrapper');

        // Toggle Active Button
        wrapper.find('.cv-tab-button').removeClass('active');
        btn.addClass('active');

        // Update Active Title and Description dynamically
        const title = btn.attr('data-title');
        const desc = btn.attr('data-desc');
        wrapper.find('#cv-horo-active-title').text(title);
        wrapper.find('#cv-horo-active-desc').text(desc);

        // Toggle Active Content
        wrapper.find('.cv-tab-content').removeClass('active').hide();
        wrapper.find(`.cv-tab-content[data-tab="${tab}"]`).addClass('active').fadeIn(200);

        // Re-initialize transit pagination if transit tab is selected
        if (tab === 'transit') {
            initTransitPagination();
        }
    });

    // ----------------------------------------------------
    // PLANETARY TRANSITS LIGHTBOX DETAILS
    // ----------------------------------------------------
    $('.cv-transit-card.cv-clickable-transit-card').on('click', function(e) {
        e.preventDefault();
        
        const card = $(this);
        const modal = $('#cv-transit-lightbox');
        
        if (!modal.length) return;

        // Retrieve properties
        const title = card.attr('data-title');
        const dateStr = card.attr('data-date');
        const prediction = card.attr('data-prediction');
        const affectedSigns = card.attr('data-affected-signs') || 'None specified.';
        const remedies = card.attr('data-remedies') || 'None specified.';
        const planet = card.attr('data-planet');

        // Populate details
        modal.find('#cv-transit-lightbox-title').text(title);
        modal.find('#cv-transit-lightbox-date').text(`Planet: ${planet} | ${dateStr}`);
        modal.find('#cv-transit-lightbox-prediction').html(prediction);
        modal.find('#cv-transit-lightbox-impact').text(affectedSigns);
        modal.find('#cv-transit-lightbox-remedies').html(remedies);

        // Open
        modal.addClass('active');
        $('html, body').addClass('cv-modal-open');
    });

    // ----------------------------------------------------
    // TRANSIT CARD PAGINATION (MOBILE ONLY)
    // ----------------------------------------------------
    function initTransitPagination() {
        $('.cv-transit-grid, .cv-transit-list').each(function() {
            const container = $(this);
            const cards = container.children('.cv-transit-card');
            
            // Clean up any existing pagination wrapper first
            container.next('.cv-transit-pagination').remove();
            
            if (window.innerWidth < 768) {
                if (cards.length > 1) {
                    const totalPages = cards.length;
                    let paginationHtml = '<div class="cv-transit-pagination">';
                    
                    // Prev Button
                    paginationHtml += '<span class="cv-transit-page-btn prev" data-action="prev">&lsaquo;</span>';
                    
                    // Page numbers
                    for (let i = 1; i <= totalPages; i++) {
                        paginationHtml += `<span class="cv-transit-page-btn page-num ${i === 1 ? 'active' : ''}" data-page="${i}">${i}</span>`;
                    }
                    
                    // Next Button
                    paginationHtml += '<span class="cv-transit-page-btn next" data-action="next">&rsaquo;</span>';
                    paginationHtml += '</div>';
                    
                    container.after(paginationHtml);
                    
                    let currentPage = 1;
                    
                    function showPage(page) {
                        currentPage = page;
                        cards.hide();
                        cards.eq(page - 1).fadeIn(200);
                        
                        const pagination = container.next('.cv-transit-pagination');
                        pagination.find('.cv-transit-page-btn.page-num').removeClass('active');
                        pagination.find(`.cv-transit-page-btn.page-num[data-page="${page}"]`).addClass('active');
                        
                        // Handle prev/next disabled state
                        pagination.find('.cv-transit-page-btn.prev').toggleClass('disabled', page === 1);
                        pagination.find('.cv-transit-page-btn.next').toggleClass('disabled', page === totalPages);
                    }
                    
                    showPage(1);
                    
                    // Page buttons click action
                    container.next('.cv-transit-pagination').on('click', '.cv-transit-page-btn', function() {
                        const btn = $(this);
                        if (btn.hasClass('disabled')) return;
                        
                        const action = btn.attr('data-action');
                        if (action === 'prev') {
                            if (currentPage > 1) showPage(currentPage - 1);
                        } else if (action === 'next') {
                            if (currentPage < totalPages) showPage(currentPage + 1);
                        } else {
                            const page = parseInt(btn.attr('data-page'));
                            showPage(page);
                        }
                    });
                } else {
                    cards.show();
                }
            } else {
                cards.show();
            }
        });
    }

    // Run pagination on load
    initTransitPagination();

    // Run pagination on resize with basic debounce
    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            initTransitPagination();
        }, 150);
    });
});
