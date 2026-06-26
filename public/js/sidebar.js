/**
 * Sidebar: scroll active menu item into view after page load / metismenu expand.
 */
(function ($) {
    'use strict';

    function getSidebarScrollElement() {
        var wrapper = document.querySelector('.vertical-menu [data-simplebar]');
        if (!wrapper) {
            return null;
        }
        if (typeof SimpleBar !== 'undefined' && SimpleBar.instances && SimpleBar.instances.get) {
            var instance = SimpleBar.instances.get(wrapper);
            if (instance) {
                return instance.getScrollElement();
            }
        }
        return wrapper.querySelector('.simplebar-content-wrapper') || wrapper;
    }

    function findActiveMenuLink() {
        var selectors = [
            '#sidebar-menu ul.sub-menu li.mm-active > a.active',
            '#sidebar-menu ul.sub-menu li.mm-active > a',
            '#sidebar-menu li.dept-item.mm-active > a.active',
            '#sidebar-menu li.dept-item.mm-active > a',
            '#sidebar-menu li.mm-active > a.active',
            '#sidebar-menu a.active'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var el = document.querySelector(selectors[i]);
            if (el) {
                return el;
            }
        }
        return null;
    }

    function expandActiveMenuParents() {
        $('#sidebar-menu a.active').each(function () {
            var $link = $(this);
            $link.closest('li').addClass('mm-active');
            $link.parents('ul.sub-menu').each(function () {
                var $sub = $(this);
                $sub.addClass('mm-show').attr('aria-expanded', 'true');
                var $parentLi = $sub.parent('li');
                $parentLi.addClass('mm-active');
                $parentLi.children('a.has-arrow').addClass('mm-active');
            });
        });
    }

    function scrollSidebarToActive() {
        expandActiveMenuParents();

        var activeLink = findActiveMenuLink();
        if (!activeLink) {
            return;
        }

        var scrollEl = getSidebarScrollElement();
        if (!scrollEl) {
            activeLink.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }

        var linkRect = activeLink.getBoundingClientRect();
        var containerRect = scrollEl.getBoundingClientRect();
        var padding = 48;
        var targetTop = scrollEl.scrollTop + (linkRect.top - containerRect.top) - padding;

        if (linkRect.top < containerRect.top + padding || linkRect.bottom > containerRect.bottom - padding) {
            scrollEl.scrollTo({
                top: Math.max(0, targetTop),
                behavior: 'smooth'
            });
        }
    }

    function initSidebarScroll() {
        // Wait for metismenu + simplebar + app.js active-state logic
        setTimeout(scrollSidebarToActive, 100);
        setTimeout(scrollSidebarToActive, 350);
        setTimeout(scrollSidebarToActive, 700);
    }

    $(document).ready(initSidebarScroll);
    $(window).on('load', scrollSidebarToActive);

})(jQuery);
