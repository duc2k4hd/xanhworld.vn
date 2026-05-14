(function () {
    function toggleSidebar() {
        const sidebar = document.getElementById('xanhworld_blog_leftSidebar');
        if (sidebar) {
            sidebar.classList.toggle('xanhworld_blog_active');
        }
    }

    window.toggleSidebar = toggleSidebar;

    document.addEventListener('click', function (event) {
        const sidebar = document.getElementById('xanhworld_blog_leftSidebar');
        const toggle = document.querySelector('.xanhworld_blog_menu-toggle');

        if (sidebar && toggle && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('xanhworld_blog_active');
        }
    });

    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (event) {
            event.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));

            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                });
            }
        });
    });

    const imageObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.xanhworld_blog_article-content img').forEach(function (img) {
        img.style.opacity = '0';
        img.style.transform = 'translateY(20px)';
        img.style.transition = 'opacity 0.6s, transform 0.6s';
        imageObserver.observe(img);
    });

    const chatBubble = document.querySelector('.xanhworld_blog_chat-bubble');
    if (chatBubble) {
        setInterval(function () {
            chatBubble.style.animation = 'pulse 1s';
            setTimeout(function () {
                chatBubble.style.animation = '';
            }, 1000);
        }, 5000);
    }

    if (!document.getElementById('blog-pulse-animation')) {
        const style = document.createElement('style');
        style.id = 'blog-pulse-animation';
        style.textContent = `
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }
        `;
        document.head.appendChild(style);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const carousel = document.getElementById('postImageCarousel');
        if (carousel) {
            const items = carousel.querySelectorAll('.xanhworld-article-carousel-item');
            const prevBtn = carousel.querySelector('.xanhworld-article-carousel-prev');
            const nextBtn = carousel.querySelector('.xanhworld-article-carousel-next');

            if (items.length > 1) {
                let currentIndex = 0;

                const showSlide = function (index) {
                    items.forEach(function (item, itemIndex) {
                        item.classList.toggle('active', itemIndex === index);
                    });
                };

                const nextSlide = function () {
                    currentIndex = (currentIndex + 1) % items.length;
                    showSlide(currentIndex);
                };

                const prevSlide = function () {
                    currentIndex = (currentIndex - 1 + items.length) % items.length;
                    showSlide(currentIndex);
                };

                if (prevBtn) {
                    prevBtn.addEventListener('click', prevSlide);
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', nextSlide);
                }
            }
        }

        const tocDesktop = document.getElementById('toc-desktop');
        const tocMobile = document.getElementById('toc-mobile');
        const tocContainer = tocDesktop || tocMobile;

        if (!tocContainer) {
            return;
        }

        const contentSections = document.querySelectorAll('.xanhworld_blog_article-content h2[id], .xanhworld_blog_article-content h3[id]');
        if (contentSections.length === 0) {
            return;
        }

        const tocLinks = new Map();
        tocContainer.querySelectorAll('a').forEach(function (link) {
            const href = link.getAttribute('href');
            if (href) {
                tocLinks.set(href.substring(1), link);
            }
        });

        let activeId = null;
        const tocObserver = new IntersectionObserver(function (entries) {
            let currentEntry = null;

            entries.forEach(function (entry) {
                if (entry.isIntersecting && entry.intersectionRatio > 0) {
                    if (!currentEntry || entry.boundingClientRect.top < currentEntry.boundingClientRect.top) {
                        currentEntry = entry;
                    }
                }
            });

            if (!currentEntry) {
                const viewportTop = window.scrollY + 100;

                entries.forEach(function (entry) {
                    const elementTop = entry.boundingClientRect.top + window.scrollY;
                    const currentTop = currentEntry ? currentEntry.boundingClientRect.top + window.scrollY : -Infinity;
                    if (elementTop <= viewportTop && elementTop > currentTop) {
                        currentEntry = entry;
                    }
                });
            }

            if (!currentEntry) {
                return;
            }

            const id = currentEntry.target.getAttribute('id');
            if (!id || id === activeId) {
                return;
            }

            activeId = id;
            tocContainer.querySelectorAll('a').forEach(function (link) {
                link.classList.remove('active');
            });

            const tocLink = tocLinks.get(id);
            if (!tocLink) {
                return;
            }

            tocLink.classList.add('active');

            if (tocContainer.scrollHeight > tocContainer.clientHeight) {
                const linkTop = tocLink.offsetTop;
                const linkHeight = tocLink.offsetHeight;
                const containerHeight = tocContainer.clientHeight;
                const scrollTop = tocContainer.scrollTop;

                if (linkTop < scrollTop) {
                    tocContainer.scrollTop = linkTop - 20;
                } else if (linkTop + linkHeight > scrollTop + containerHeight) {
                    tocContainer.scrollTop = linkTop - containerHeight + linkHeight + 20;
                }
            }
        }, {
            rootMargin: '-100px 0px -60% 0px',
            threshold: [0, 0.1, 0.5, 1],
        });

        contentSections.forEach(function (section) {
            tocObserver.observe(section);
        });

        document.querySelectorAll('.xanhworld_blog_article-content img:not([loading])').forEach(function (img) {
            img.setAttribute('loading', 'lazy');
        });

        tocContainer.addEventListener('click', function (event) {
            const link = event.target.closest('a');
            if (!link || !link.hash) {
                return;
            }

            event.preventDefault();
            const target = document.getElementById(link.hash.substring(1));
            if (!target) {
                return;
            }

            window.scrollTo({
                top: target.getBoundingClientRect().top + window.pageYOffset - 100,
                behavior: 'smooth',
            });

            setTimeout(function () {
                const id = target.getAttribute('id');
                if (!id) {
                    return;
                }

                tocContainer.querySelectorAll('a').forEach(function (tocLink) {
                    tocLink.classList.remove('active');
                });

                const activeLink = tocLinks.get(id);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }, 500);
        });
    });
})();
