import './bootstrap';

import {Alpine} from "alpinejs";


window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('menu', {
        mobileMenuOpen: false
    })

    Alpine.data('menuState', () => ({
        get mobileMenuOpen() {
            return Alpine.store('menu').mobileMenuOpen
        },
        set mobileMenuOpen(value) {
            Alpine.store('menu').mobileMenuOpen = value
        }
    }))

    Alpine.data('stickyHeader', () => ({
        isSticky: false,
        init() {
            window.addEventListener('scroll', () => {
                this.isSticky = window.scrollY > 100
            })
        },
    }))
})

Alpine.start()

