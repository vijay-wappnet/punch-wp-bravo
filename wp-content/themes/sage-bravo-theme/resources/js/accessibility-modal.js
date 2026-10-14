/**
 * Accessibility Modal
 *
 * Provides accessibility options for font size, contrast, dyslexia font, line height, and text justification
 */

import { Modal } from 'bootstrap';

// Mark the buttons matching `selector` as (in)active
const setActive = (selector, active) => {
  document.querySelectorAll(selector).forEach((btn) => btn.classList.toggle('active', active));
};

class AccessibilityModal {
  constructor() {
    this.modalElement = document.getElementById('accessibilityModal');
    this.settings = {
      contrast: 'default',
      textSize: 'default',
      font: 'default',
      lineSpacing: 'default',
      justification: 'default',
    };
    this.storageKey = 'punch_accessibility_settings';

    // Only initialize if modal exists in the DOM
    if (this.modalElement) {
      this.loadSavedSettings();
      this.bindEvents();
      this.initAccessibilityControls();

      // Apply saved settings when DOM is fully loaded to ensure they take effect
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => this.applyAllSettings());
      } else {
        this.applyAllSettings();
      }
    }
  }

  loadSavedSettings() {
    try {
      const savedSettings = localStorage.getItem(this.storageKey);
      if (savedSettings) {
        this.settings = JSON.parse(savedSettings);
        //console.log('Loaded accessibility settings:', this.settings);
      }
    } catch (error) {
      //console.error('Error loading accessibility settings:', error);
    }
  }

  saveSettings() {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(this.settings));
      //console.log('Saved accessibility settings:', this.settings);
    } catch (error) {
     // console.error('Error saving accessibility settings:', error);
    }
  }

  applyAllSettings() {
    // Apply each setting
    Object.keys(this.settings).forEach(setting => {
      const value = this.settings[setting];
      this.applySettings(setting, value);

      // Update the UI to reflect current settings
      if (value !== 'default') {
        setActive(`.btn-setting[data-setting="${setting}"][data-value="${value}"]`, true);
        setActive(`.btn-setting[data-setting="${setting}"][data-value="default"]`, false);
      } else {
        // If default, make sure default button is active
        setActive(`.btn-setting[data-setting="${setting}"][data-value="default"]`, true);
        setActive(`.btn-setting[data-setting="${setting}"]:not([data-value="default"])`, false);
      }
    });
  }

  bindEvents() {
    const modalEl = document.getElementById('accessibilityModal');
    if (!modalEl) return;

    // Use getOrCreateInstance for reliability
    const modalInstance = Modal.getOrCreateInstance(modalEl);

    // Listen for modal hidden event
    modalEl.addEventListener('hidden.bs.modal', () => {
      this.applyAllSettings();
    });

    // Listen for modal shown event
    modalEl.addEventListener('shown.bs.modal', () => {
      Object.keys(this.settings).forEach(setting => {
        const value = this.settings[setting];
        document.querySelectorAll(`.btn-setting[data-setting="${setting}"]`).forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`.btn-setting[data-setting="${setting}"][data-value="${value}"]`);
        if (activeBtn) activeBtn.classList.add('active');
      });
    });

    // Handle modal open click (support both Bootstrap 5 and legacy attributes)
    const modalTriggers = [
      ...document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#accessibilityModal"]'),
      ...document.querySelectorAll('[data-toggle="modal"][data-target="#accessibilityModal"]')
    ];
    modalTriggers.forEach(el => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        modalInstance.show();
      });
    });

    // Ensure close button works
    document.querySelectorAll('.btn-close, [data-dismiss="modal"]').forEach(el => {
      el.addEventListener('click', () => modalInstance.hide());
    });

    // Handle search functionality
    document.querySelectorAll('.footer-search-input').forEach(input => {
      input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          const query = this.value.trim();
          if (query !== '') {
            const baseUrl = window.location.origin;
            window.location.href = baseUrl + '/?s=' + encodeURIComponent(query);
          }
        }
      });
    });
  }

  initAccessibilityControls() {
    // Handle button clicks for accessibility settings
    document.querySelectorAll('.btn-setting').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const setting = btn.getAttribute('data-setting');
        const value = btn.getAttribute('data-value');

        // Update button active states
        document.querySelectorAll(`.btn-setting[data-setting="${setting}"]`).forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update settings
        this.settings[setting] = value;

        // Apply settings to the page
        this.applySettings(setting, value);

        // Save settings to localStorage
        this.saveSettings();
      });
    });
  }

  applySettings(setting, value) {
    //console.log(`Setting ${setting} to ${value}`);

    // Apply the settings to the website
    switch(setting) {
      case 'contrast':
        this.applyContrastSetting(value);
        break;
      case 'textSize':
        this.applyTextSizeSetting(value);
        break;
      case 'font':
        this.applyFontSetting(value);
        break;
      case 'lineSpacing':
        this.applyLineSpacingSetting(value);
        break;
      case 'justification':
        this.applyJustificationSetting(value);
        break;
    }
  }

  applyContrastSetting(value) {
    // Remove any existing contrast classes
    document.body.classList.remove('contrast-higher', 'contrast-inverted');

    // Apply new contrast setting
    if (value !== 'default') {
      document.body.classList.add(`contrast-${value}`);
    }
  }

  applyTextSizeSetting(value) {
    // Remove any existing text size classes
    document.body.classList.remove('text-medium', 'text-large');

    // Apply new text size setting
    if (value !== 'default') {
      document.body.classList.add(`text-${value}`);
    }
  }

  applyFontSetting(value) {
    // Remove any existing font classes
    document.body.classList.remove('font-adapted');

    // Apply new font setting
    if (value !== 'default') {
      document.body.classList.add(`font-${value}`);
    }
  }

  applyLineSpacingSetting(value) {
    // Remove any existing line spacing classes
    document.body.classList.remove('line-spacing-adapted');

    // Apply new line spacing setting
    if (value !== 'default') {
      document.body.classList.add('line-spacing-adapted');
    }
  }

  applyJustificationSetting(value) {
    // Remove any existing justification classes
    document.body.classList.remove('justify-text');

    // Apply new justification setting
    if (value !== 'default') {
      document.body.classList.add('justify-text');
    }
  }
}

export default new AccessibilityModal();
