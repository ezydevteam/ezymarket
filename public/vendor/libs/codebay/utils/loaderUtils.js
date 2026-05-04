// Simple Bootstrap Spinner Utility
(function() {
    'use strict';
    
    window.loaderUtils = {
        
        // Create simple Bootstrap spinner element
        createSpinner(options = {}) {
            const config = {
                size: 'md', // sm, md, lg
                color: 'primary', // primary, success, danger, warning, info, light, dark
                type: 'border', // border, grow
                ...options
            };
            
            const spinner = document.createElement('div');
            
            // Base classes
            let classes = [`spinner-${config.type}`];
            
            // Size
            if (config.size === 'sm') {
                classes.push(`spinner-${config.type}-sm`);
            }
            
            // Color
            classes.push(`text-${config.color}`);
            
            spinner.className = classes.join(' ');
            spinner.setAttribute('role', 'status');
            spinner.innerHTML = '<span class="visually-hidden">Loading...</span>';
            
            return spinner;
        },
        
        // Show spinner in container (replaces content)
        showInContainer(element, options = {}) {
			if (!element) return;
			
			element.dataset.originalContent = element.innerHTML;
			
			const config = {
				center: true,
				padding: true,
				...options
			};
			
			const container = document.createElement('div');
			if (config.center) container.className = 'd-flex justify-content-center align-items-center';
			if (config.padding) container.className += ' p-4';
			
			const spinner = this.createSpinner(options);
			container.appendChild(spinner);
			
			element.innerHTML = '';
			element.appendChild(container);
		},
		
		showLoadMore(element, show, options = {}) {
			if (!element) return;
			
			const config = {
				center: true,
				padding: true,
				loaderClass: 'load-more-spinner',
				message: 'Loading more...',
				color: 'primary',
				...options
			};
			
			let loader = element.querySelector(`.${config.loaderClass}`);
			
			if (show && !loader) {
				loader = document.createElement('div');
				loader.className = config.loaderClass; 
				
				if (config.center) {
					loader.classList.add('d-flex', 'justify-content-center', 'align-items-center');
				}
				if (config.padding) {
					loader.classList.add('p-4');
				}
				
				loader.appendChild(this.createSpinner({
					color: config.color,
					message: config.message
				}));
				
				element.appendChild(loader);
			} else if (!show && loader) {
				loader.remove();
			}
		},
		
		/*createSpinner(options = {}) {
			const config = {
				color: 'primary',
				size: 'md',
				message: 'Loading...',
				...options
			};
			
			const spinner = document.createElement('div');
			spinner.className = `spinner-border text-${config.color}`;
			spinner.setAttribute('role', 'status');
			
			const span = document.createElement('span');
			span.className = 'visually-hidden';
			span.textContent = config.message;
			spinner.appendChild(span);
			
			return spinner;
		},*/
        
        // Show spinner overlay (preserves content)
        showOverlay(element, options = {}) {
            if (!element) return;
            
            // Remove existing overlay
            this.hideOverlay(element);
            
            const overlay = document.createElement('div');
            overlay.className = 'spinner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center';
            overlay.style.backgroundColor = 'rgba(255,255,255,0.8)';
            overlay.style.zIndex = '1000';
            
            const spinner = this.createSpinner(options);
            overlay.appendChild(spinner);
            
            element.style.position = 'relative';
            element.appendChild(overlay);
        },
        
        // Hide overlay spinner
        hideOverlay(element) {
            if (!element) return;
            const overlay = element.querySelector('.spinner-overlay');
            if (overlay) overlay.remove();
        },
        
        // Show spinner in button
        showInButton(button, options = {}) {
            if (!button) return;
            
            const config = {
                size: 'sm',
                color: 'light',
                ...options
            };
            
            button.disabled = true;
            button.setAttribute('data-original-html', button.innerHTML);
            
            const spinner = this.createSpinner(config);
            button.innerHTML = '';
            button.appendChild(spinner);
        },
        
        // Hide spinner from button
        hideFromButton(button) {
            if (!button) return;
            
            button.disabled = false;
            const originalHtml = button.getAttribute('data-original-html');
            if (originalHtml) {
                button.innerHTML = originalHtml;
                button.removeAttribute('data-original-html');
            }
        },
        
        // Quick inline spinner
        inline(options = {}) {
            const config = {
                size: 'sm',
                ...options
            };
            return this.createSpinner(config);
        }
    };
    
})();