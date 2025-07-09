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
})

Alpine.start()

