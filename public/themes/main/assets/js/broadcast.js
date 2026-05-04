(function($) {
    "use strict";

    class BroadcastManager {
		constructor() {
			this.bellIcon = document.getElementById('notificationBell');
			this.notificationBadge = document.getElementById('notificationBadge');
			this.notificationDropdown = document.getElementById('notificationDropdown');
			this.notificationList = document.getElementById('notificationList');
			this.markAllReadNotifBtn = document.getElementById('markAllReadBtn');
			this.headerUnreadNotifCount = document.getElementById('headerUnreadCount');
			this.soundToggleBtn = document.getElementById('soundToggleBtn');
			this.soundIcon = document.getElementById('soundIcon');
			this.chatIcon = document.getElementById('chatIcon');
			this.chatDropdown = document.getElementById('chatDropdown');
			this.unreadMessageBadge = document.getElementById('unreadMessageBadge');
			this.chatDropdownMessages = document.getElementById('recent-messages');
			this.isFullPageMessenger = document.getElementById('conversations-list');

			this.isNotificationPageEnabled = this.bellIcon && this.notificationDropdown;
			this.isChatDropdownEnabled = this.chatIcon && this.chatDropdown;

			this.soundEnabled = true;
			this.isOpen = false;
			this.isSending = false;
			this.notifications = [];
			this.unreadCount = 0;
			this.userId = null;
			this.username = null;
			this.conversations = [];
			this.currentConversation = null;
			this.currentRecipient = null;
			this.chatUnreadCount = 0;
			this.csrfToken = null;
			this.ably = null;
			this.ablyKey = null;
			this.searchTimeout = null;
			this.conversationTimestampInterval = null;
			this.presenceUpdateInterval = null;
            this.presenceTimestampRefreshInterval = null;
            this.currentPage = 1;
            this.isLoadingMore = false;
            this.hasMorePages = true;
			this.soundPath = '/themes/basic/sounds/notification.mp3';

			if (this.isNotificationPageEnabled || this.isChatDropdownEnabled || this.isFullPageMessenger) {
				this.init();
			}
		}

		init() {
			this.username = document.querySelector('meta[name="user-username"]')?.getAttribute('content') || 'guest';
			this.userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
			this.ablyKey = document.querySelector('meta[name="ably-key"]')?.getAttribute('content');
			this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

			this.soundEnabled = localStorage.getItem('notificationSoundEnabled') !== 'false';
			this.updateSoundButton();

			if (this.bellIcon) {
				this.bellIcon.addEventListener('click', (e) => {
					e.stopPropagation();
					this.toggleNotificationDropdown();
				});
			}

			document.addEventListener('click', (e) => {
				if (!e.target.closest('.notification-wrapper')) {
					this.closeNotificationDropdown();
				}
			});

			if (this.chatIcon) {
				this.chatIcon.addEventListener('click', (e) => {
					e.stopPropagation();
					this.toggleChatDropdown();
				});
			}

			document.addEventListener('click', (e) => {
				if (!e.target.closest('.chatbox-wrapper')) {
					this.closeChatDropdown();
				}
			});

			if (this.markAllReadNotifBtn) {
				this.markAllReadNotifBtn.addEventListener('click', () => {
					this.markAllAsRead();
				});
			}

			if (this.soundToggleBtn) {
				this.soundToggleBtn.addEventListener('click', (e) => {
					e.stopPropagation();
					this.toggleSound();
				});
			}

					if (this.isNotificationPageEnabled && this.notificationList) {
				this.loadNotifications();
				this.startPolling();
			}

			if (this.isFullPageMessenger) {
				this.initializeFullPageMessenger();
				this.initializeEnhancedMessenger();
				this.initializeInfiniteScroll();
			}

			this.initMessageUserButtons();
			this.initializeUserSearch();
			this.initializeAbly();
			this.startConversationTimestampRefresh();
			this.startPresenceTimestampRefresh();
			//this.registerCleanupEvents();
		}

		initializeEnhancedMessenger() {
			this.initializeMessengerUI();
			this.initializeEmojiPicker();
			this.initializeEnhancedMessageInput();
			this.initializeActionHandlers();
			this.initializeTypingIndicator();
			this.initializeMobileNavigation();
		}

		initMessageUserButtons() {
			document.addEventListener('click', (e) => {
				const messageBtn = e.target.closest('.message-user-btn');

				if (messageBtn) {
					e.preventDefault();
					const userId = messageBtn.getAttribute('data-user-id');

					if (userId) {
						window.location.href = `/chatbox?user_id=${userId}`;
					}
				}
			});
		}

		initializeMessengerUI() {
			window.scrollChatToBottom = function(smooth = true) {
				const container = document.getElementById('chat-messages');
				if (!container) return;

				if (smooth) {
					container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
				} else {
					container.scrollTop = container.scrollHeight;
				}
			};

			window.isUserAtBottom = function() {
				const container = document.getElementById('chat-messages');
				if (!container) return false;

				const threshold = 100;
				return container.scrollHeight - container.scrollTop - container.clientHeight <= threshold;
			};
		}

		initializeEnhancedMessageInput() {
			const messageInput = document.getElementById('message-input');
			const sendBtn = document.getElementById('send-btn');
			const messageForm = document.getElementById('message-form');

			if (!messageInput || !sendBtn || !messageForm) return;

			const updateSendButton = () => {
				const hasContent = messageInput.value.trim().length > 0;
				sendBtn.disabled = !hasContent || this.isSending;
				sendBtn.style.opacity = sendBtn.disabled ? '0.5' : '1';
			};


			updateSendButton();
			messageInput.addEventListener('input', updateSendButton);
			messageInput.addEventListener('keypress', (e) => {
				if (e.key === 'Enter' && !e.shiftKey) {
					e.preventDefault();
					if (messageInput.value.trim() && !this.isSending) {
						this.handleEnhancedMessageSend();
					}
				}
			});

			sendBtn.addEventListener('click', (e) => {
				e.preventDefault();
				this.handleEnhancedMessageSend();
			});

			messageForm.addEventListener('submit', (e) => {
				e.preventDefault();
				this.handleEnhancedMessageSend();
			});

			this.messageInput = messageInput;
			this.sendBtn = sendBtn;
			this.updateSendButton = updateSendButton;
		}

		async handleEnhancedMessageSend() {
			if (this.isSending) return;

			const content = this.messageInput.value.trim();
			if (!content || !this.currentRecipient) return;

			this.isSending = true;
			this.updateSendButton();
			window.loaderUtils.showInButton(this.sendBtn);

			try {
				await this.sendMessage();
				this.messageInput.value = '';
			} catch (error) {
				toastr.error('Failed to send message');
			} finally {
				this.isSending = false;
				window.loaderUtils.hideFromButton(this.sendBtn);
				this.updateSendButton();
			}
		}

		initializeEmojiPicker() {
			const emojiBtn = document.getElementById('emoji-btn');
			const emojiPicker = document.getElementById('emoji-picker');
			const messageInput = document.getElementById('message-input');

			if (!emojiBtn || !emojiPicker || !messageInput) return;

			let picker = null;

			emojiBtn.addEventListener('click', (e) => {
				e.preventDefault();

				if (emojiPicker.classList.contains('d-none')) {
					if (!picker) {
                        const emojis = ['👍','👎','👌','👏','🤝','💯','🆗','✅','❌','🙏','💲','😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙','🥲','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠',,'💀','👽','🤖','🎃','😺','😻','😿','😾️','❤️','💚','🖤','💔','💞','💘'];
						const emojiGrid = document.createElement('div');
						emojiGrid.className = 'emoji-grid';

						emojis.forEach(emoji => {
							const emojiButton = document.createElement('button');
							emojiButton.textContent = emoji;
							emojiButton.className = 'emoji-btn-product';
							emojiButton.type = 'button';
							emojiButton.addEventListener('click', (e) => {
								e.preventDefault();
								this.insertEmoji(emoji);
							});
							emojiGrid.appendChild(emojiButton);
						});

						emojiPicker.appendChild(emojiGrid);
						picker = emojiGrid;
					}
					emojiPicker.classList.remove('d-none');
				} else {
					emojiPicker.classList.add('d-none');
				}
			});

			document.addEventListener('click', (e) => {
				if (!emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
					emojiPicker.classList.add('d-none');
				}
			});
		}

		insertEmoji(emoji) {
			const messageInput = document.getElementById('message-input');
			const cursorPos = messageInput.selectionStart;
			const currentValue = messageInput.value;
			const newValue = currentValue.slice(0, cursorPos) + emoji + currentValue.slice(cursorPos);

			messageInput.value = newValue;
			messageInput.focus();

			const newCursorPos = cursorPos + emoji.length;
			messageInput.setSelectionRange(newCursorPos, newCursorPos);

			const event = new Event('input', { bubbles: true });
			messageInput.dispatchEvent(event);

			document.getElementById('emoji-picker').classList.add('d-none');
		}

		initializeActionHandlers() {
			const blockBtn = document.getElementById('block-user-btn');
			const deleteBtn = document.getElementById('delete-conversation');
			const viewProfileBtn = document.getElementById('view-profile');

			if (blockBtn) {
				blockBtn.addEventListener('click', (e) => {
					e.preventDefault();
					if (!this.currentRecipient) return;

					const isCurrentlyBlocked = blockBtn.getAttribute('data-user-blocked') === 'true';
					const user = this.currentRecipient;

					if (isCurrentlyBlocked) {
						this.showConfirmModal(
							'Unblock User',
							`Are you sure you want to unblock ${user.name}? You will be able to receive messages from them again.`,
							'Unblock',
							() => this.unblockUser(user.id)
						);
					} else {
						this.showConfirmModal(
							'Block User',
							`Are you sure you want to block ${user.name}? You won't receive messages from them anymore.`,
							'Block',
							() => this.blockUser(user.id)
						);
					}
				});
			}

			if (deleteBtn) {
				deleteBtn.addEventListener('click', (e) => {
					e.preventDefault();
					if (!this.currentConversation) return;

					this.showConfirmModal(
						'Delete Conversation',
						'Are you sure you want to delete this conversation? This action cannot be undone.',
						'Delete',
						() => this.deleteConversation(this.currentConversation)
					);
				});
			}

			if (viewProfileBtn) {
				viewProfileBtn.addEventListener('click', (e) => {
					e.preventDefault();
					if (!this.currentRecipient) return;
					window.open(`/user/${this.currentRecipient.username}`, '_blank');
				});
			}
		}

		showContainerLoader(element) {
			if (!element) return;

			window.loaderUtils.showInContainer(element, {
                color: 'success',
                size: 'md'
            });
		}

		showLoadMoreLoader(element, show, loaderClass='load-more-conversations') {
			if (!element) return;

			window.loaderUtils.showLoadMore(element, show, {
                loaderClass: loaderClass,
                color: 'success'
            });
		}

		initializeMobileNavigation() {
			window.openMobileChat = () => {
				document.querySelector('.conversation-section')?.classList.add('mobile-hidden');
				document.querySelector('.chat-window')?.classList.add('mobile-active');
			};

			window.closeMobileChat = () => {
				document.querySelector('.conversation-section')?.classList.remove('mobile-hidden');
				document.querySelector('.chat-window')?.classList.remove('mobile-active');
			};

			window.addEventListener('resize', () => {
				if (window.innerWidth > 767.98) {
					document.querySelector('.conversation-section')?.classList.remove('mobile-hidden');
					document.querySelector('.chat-window')?.classList.remove('mobile-active');
				}
			});
		}

		initializeInfiniteScroll() {
			const conversationsList = document.getElementById('conversations-list');
			if (!conversationsList) return;

			let scrollTimeout;
			conversationsList.addEventListener('scroll', () => {
				if (scrollTimeout) return;

				scrollTimeout = setTimeout(() => {
					this.handleScroll();
					scrollTimeout = null;
				}, 100);
			});
		}

		handleScroll() {
			const container = document.getElementById('conversations-list');
			if (!container || this.isLoadingMore || !this.hasMorePages) return;

			const scrollTop = container.scrollTop;
			const scrollHeight = container.scrollHeight;
			const clientHeight = container.clientHeight;

			if (scrollTop + clientHeight >= scrollHeight - 100) {
				this.loadMoreConversations();
			}
		}

		showConfirmModal(title, message, actionText, callback) {
			const modal = new bootstrap.Modal(document.getElementById('confirmModal'));

			document.getElementById('confirmModalTitle').textContent = title;
			document.getElementById('confirmModalMessage').textContent = message;

			const actionBtn = document.getElementById('confirmModalAction');
			actionBtn.textContent = actionText;

			const newActionBtn = actionBtn.cloneNode(true);
			actionBtn.parentNode.replaceChild(newActionBtn, actionBtn);

			newActionBtn.addEventListener('click', () => {
				modal.hide();
				callback();
			});

			modal.show();
		}

		showBlockedState() {
			const messageInput = document.getElementById('message-input');
			const sendBtn = document.getElementById('send-btn');
			const emojiBtn = document.getElementById('emoji-btn');

			if (messageInput) {
				messageInput.disabled = true;
				messageInput.value = 'You\'ve blocked this person. Unblock to start conversation';
				messageInput.placeholder = '';
				messageInput.classList.add('blocked-placeholder');
				messageInput.style.backgroundColor = '#f8f9fa';
			}

			if (sendBtn) sendBtn.style.display = 'none';
			if (emojiBtn) emojiBtn.style.display = 'none';
		}

		showRestrictedState() {
			const messageInput = document.getElementById('message-input');
			const sendBtn = document.getElementById('send-btn');
			const emojiBtn = document.getElementById('emoji-btn');

			if (messageInput) {
				messageInput.disabled = true;
				messageInput.value = 'You cann\'t continue this conversation anymore.';
				messageInput.placeholder = '';
				messageInput.style.backgroundColor = '#f8f9fa';
			}

			if (sendBtn) sendBtn.style.display = 'none';
			if (emojiBtn) emojiBtn.style.display = 'none';
		}

		showNormalState() {
			const messageInput = document.getElementById('message-input');
			const sendBtn = document.getElementById('send-btn');
			const emojiBtn = document.getElementById('emoji-btn');

			if (messageInput) {
				messageInput.disabled = false;
				messageInput.value = '';
				messageInput.placeholder = 'Type a message...';
				messageInput.style.backgroundColor = '';
			}

			if (sendBtn) sendBtn.style.display = '';
			if (emojiBtn) emojiBtn.style.display = '';
		}

		updateBlockButton(user) {
			const blockBtn = document.getElementById('block-user-btn');
			const blockIcon = document.getElementById('block-icon');
			const blockText = document.getElementById('block-text');

			if (!blockBtn || !blockIcon || !blockText) {
                return;
            }

			const isBlocked = user.is_blocked ?? false;

			if (isBlocked) {
				blockBtn.setAttribute('data-user-blocked', 'true');
				blockBtn.className = 'dropdown-product text-success';
				blockIcon.className = 'bi bi-check-circle me-2';
				blockText.textContent = 'Unblock User';
			} else {
				blockBtn.setAttribute('data-user-blocked', 'false');
				blockBtn.className = 'dropdown-product text-warning';
				blockIcon.className = 'bi bi-ban me-2';
				blockText.textContent = 'Block User';
			}
		}

		async blockUser(userId) {
			try {
				const response = await fetch('/chatbox/block', {
					method: 'POST',
					headers: {
					    'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': this.csrfToken,
					},
					body: JSON.stringify({ user_id: userId })
				});

				const data = await response.json();

				if (response.ok) {
					toastr.success('User blocked successfully');

					if (this.currentRecipient) {
						this.currentRecipient.is_blocked = true;
					}

					this.updateBlockButton(this.currentRecipient);
					this.showBlockedState();

					const chatMessages = document.getElementById('chat-messages');
					if (chatMessages) {
						chatMessages.innerHTML = `
							<div class="empty-state">
							    <i class="bi bi-ban-fill text-danger"></i>
								<h5 class="mt-3">User Blocked</h5>
								<p>You have blocked this user. You won't receive messages from them.</p>
							</div>
						`;
					}

					this.loadConversations();
				} else {
					toastr.error(data.error);
				}
			} catch (error) {
				toastr.error('Failed to block user');
			}
		}

		async unblockUser(userId) {
			try {
				const response = await fetch('/chatbox/unblock', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': this.csrfToken,
						'Accept': 'application/json'
					},
					body: JSON.stringify({
					    user_id: userId
					})
				});

				const data = await response.json();

				if (response.ok) {
					toastr.success('User unblocked successfully');

					if (this.currentRecipient) {
						this.currentRecipient.is_blocked = false;
					}

					this.updateBlockButton(this.currentRecipient);
					this.showNormalState();

					const chatMessages = document.getElementById('chat-messages');
                    if (chatMessages) {
                        chatMessages.innerHTML = '';
                    }

					this.loadConversations();
				} else {
					toastr.error(data.error);
				}
			} catch (error) {
				toastr.error('Failed to unblock user');
			}
		}

		async deleteConversation(conversationId) {
			try {
				const response = await fetch(`/chatbox/conversation/${conversationId}`, {
					method: 'DELETE',
					headers: {
						'X-CSRF-TOKEN': this.csrfToken,
						'Accept': 'application/json'
					}
				});

				const data = await response.json();

				if (response.ok) {
					toastr.success('Chats deleted successfully');

					const conversationElement = document.querySelector(`[data-id="${conversationId}"]`);
					if (conversationElement) {
						conversationElement.remove();
					}

					if (this.isChatDropdownEnabled) {
                        this.loadRecentMessages();
                    }

					this.currentConversation = null;
					this.currentRecipient = null;

					document.getElementById('chat-header')?.classList.add('d-none');
					document.getElementById('message-form-container')?.classList.add('d-none');

					const chatMessages = document.getElementById('chat-messages');
					if (chatMessages) {
						chatMessages.innerHTML = `
							<div class="empty-state">
								<i class="bi bi-chat-square-dots-fill"></i>
								<h5 class="mt-3">Conversation Deleted</h5>
								<p>Select another conversation to continue messaging</p>
							</div>
						`;
					}

				} else {
					toastr.error(data.error);
				}
			} catch (error) {
				toastr.error('Failed to delete conversation');
			}
		}

		// Safe JSON parser
		safeJSONParse(text, fallback = null) {
			try {
				return JSON.parse(text);
			} catch (e) {
				console.error('JSON parsing failed:', e);
				console.error('Raw response:', text);
				return fallback;
			}
		}

		// Helper function to escape HTML - Safe version
		escapeHtml(text) {
			if (!text || typeof text !== 'string') {
				return '';
			}

			return text
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#39;');
		}

		truncateString(str, maxLength = 20) {
			if (!str) return '';
			return str.length > maxLength ? str.substring(0, maxLength) + '...' : str;
		}

		initializeFullPageMessenger() {
			this.loadConversations();
			this.setupMessengerEvents();
			this.checkUrlParams();
		}

		setupMessengerEvents() {
			document.addEventListener('click', (e) => {
				const conversationproduct = e.target.closest('.conversation-product');
				if (conversationproduct) {
					const conversationId = conversationproduct.dataset.id;
					try {
						const userData = this.safeJSONParse(conversationproduct.dataset.user);
						if (userData) {
							this.openConversation(conversationId, userData);
						}
					} catch (error) {
						console.error('Failed to parse user data:', error);
					}
				}
			});

			const messageForm = document.querySelector('#message-form');
			if (messageForm && !this.messageInput) {
				messageForm.addEventListener('submit', (e) => {
					e.preventDefault();
					this.sendMessage();
				});
			}
		}

		async checkUrlParams() {
			const urlParams = new URLSearchParams(window.location.search);
			const chatId = urlParams.get('chat');
			const userId = urlParams.get('user_id');

			if (chatId) {
				setTimeout(() => {
					this.setActiveConversation(chatId);
				}, 500);
			} else if (userId) {
				try {
					await this.loadConversations();

					const conversations = document.querySelectorAll('.conversation-product');
					let foundConversation = null;

					conversations.forEach(conv => {
						try {
							const userData = JSON.parse(conv.dataset.user);
							if (userData.id == userId) {
								foundConversation = conv.dataset.id;
							}
						} catch (e) {
							// ignore parsing errors
						}
					});

					if (foundConversation) {
						this.setActiveConversation(foundConversation);
						window.history.replaceState({}, '', `/chatbox?chat=${foundConversation}`);
					} else {
						const response = await fetch('/chatbox', {
							method: 'POST',
							headers: {
								'Accept': 'application/json',
								'Content-Type': 'application/json',
								'X-Requested-With': 'XMLHttpRequest',
								'X-CSRF-TOKEN': this.csrfToken
							},
							body: JSON.stringify({ recipient_id: userId })
						});

						const data = await response.json();

						if (data.success && data.conversation_id) {
							await this.loadConversations();
							setTimeout(() => {
								this.setActiveConversation(data.conversation_id);
								window.history.replaceState({}, '', `/chatbox?chat=${data.conversation_id}`);
							}, 500);
						} else {
							toastr.error(data.error || 'Failed to start conversation');
						}
					}
				} catch (error) {
					toastr.error('Failed to load conversations');
				}
			}
		}

		setActiveConversation(conversationId) {
			document.querySelectorAll('.conversation-product.active').forEach(el => {
				el.classList.remove('active');
			});

			const conversationEl = document.querySelector(`[data-id="${conversationId}"]`);
			if (conversationEl) {
				conversationEl.classList.add('active');
				conversationEl.click();
			}
		}

		initializeUserSearch() {
			const searchInput = document.getElementById('user-search');
			const resultsContainer = document.getElementById('user-results');
			const noResultsDiv = document.getElementById('no-results');

			if (!searchInput) return;

			searchInput.addEventListener('input', (e) => {
				const query = e.target.value.trim();

				if (this.searchTimeout) {
					clearTimeout(this.searchTimeout);
				}

				if (noResultsDiv) noResultsDiv.classList.add('d-none');

				if (query.length === 0) {
					this.showDefaultSearchState();
					return;
				}

				if (query.length < 2) {
					if (resultsContainer) {
						resultsContainer.innerHTML = '<div class="text-center py-3 text-muted"><small>Type at least 2 characters</small></div>';
					}
					return;
				}

				this.searchTimeout = setTimeout(() => {
					this.searchUsers(query);
				}, 300);
			});

			const newChatModal = document.getElementById('newChatModal');
			if (newChatModal) {
				newChatModal.addEventListener('hidden.bs.modal', () => {
					searchInput.value = '';
					this.showDefaultSearchState();
				});
			}
		}

		showDefaultSearchState() {
			const resultsContainer = document.getElementById('user-results');
			if (resultsContainer) {
				resultsContainer.innerHTML = `
					<div class="text-muted text-center py-4">
						<i class="bi bi-search fs-4 opacity-75"></i>
						<div class="mt-2">Start typing to search users</div>
					</div>
				`;
			}
		}

		async searchUsers(query) {
			const resultsContainer = document.getElementById('user-results');
			const noResultsDiv = document.getElementById('no-results');

			try {
				if (resultsContainer) resultsContainer.innerHTML = '';
				this.showContainerLoader(resultsContainer);

				const response = await fetch('/chatbox/api/users/search', {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					},
					body: JSON.stringify({ query: query })
				});

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}`);
				}

				const data = await response.json();

				if (data.users && data.users.length > 0) {
					this.renderSearchResults(data.users);
				} else {
				    if (resultsContainer) resultsContainer.innerHTML = '';
					if (noResultsDiv) noResultsDiv.classList.remove('d-none');
				}

			} catch (error) {
				if (resultsContainer) {
					resultsContainer.innerHTML = `
						<div class="text-center py-3 text-danger">
							<i class="bi bi-exclamation-triangle"></i>
							<div class="mt-1">Search failed. Please try again.</div>
						</div>
					`;
				}
			}
		}

		renderSearchResults(users) {
			const resultsContainer = document.getElementById('user-results');
			if (!resultsContainer) return;

			const html = users.map(user => {
				let priorityBadge = '';
				let productClass = 'user-search-product';

				if (user.relationship === 'following') {
					priorityBadge = '<span class="badge priority-following fw-light ms-1 py-1">Following</span>';
					productClass += ' following';
				} else if (user.relationship === 'follower') {
					priorityBadge = '<span class="badge priority-follower fw-light ms-1 py-1">Follower</span>';
					productClass += ' follower';
				}

				const userFullName = user.name || 'unknown';
				const userUsername = user.username;
				const userId = user.id || '';
				const userAvatar = user.avatar;

				return `
					<div class="${productClass}" onclick="BroadcastManager.selectUserForChat(${userId}, '${this.escapeHtml(userFullName)}', '${userUsername}', '${userAvatar}')">
						<div class="d-flex align-products-center">
							<div class="position-relative flex-shrink-0">
								<img src="${userAvatar}" class="rounded-circle" width="20" height="20">
								${user.is_online ? '<span class="position-absolute bg-success rounded-circle" style="width:14px;height:14px;bottom:2px;right:2px;border:2px solid white;"></span>' : ''}
							</div>
							<div class="flex-grow-1 ms-1">
								<p class="d-inline fw-500 mb-0">${this.escapeHtml(userFullName)}</p>
								<span class="text-muted fst-italic">@${userUsername}</span>
								${priorityBadge}
							</div>
						</div>
					</div>
				`;
			}).join('');

			resultsContainer.innerHTML = html;
		}

		selectUserForChat(userId, name, username, avatar) {
			const modal = document.getElementById('newChatModal');
			if (modal && window.bootstrap) {
				const modalInstance = bootstrap.Modal.getInstance(modal);
				if (modalInstance) {
					modalInstance.hide();
				}
			}

			this.startNewConversation(userId, name, username, avatar);
		}

		async startNewConversation(userId, name, username, avatar) {
			try {
				const response = await fetch('/chatbox', {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken,
					},
					body: JSON.stringify({
						recipient_id: userId
					})
				});

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}: ${response.statusText}`);
				}

				const data = await response.json();
				this.renderRecentMessages(data.data);

				if (data.error) {
					toastr.error(data.error);
					return;
				}

				if (window.location.pathname.includes('chatbox')) {
					await this.loadConversations();

					const otherUser = {
						id: userId,
						name: name,
						username: username,
						avatar: avatar,
						is_blocked: false
					};

					await this.openConversation(data.conversation_id, otherUser);

				} else {
					window.location.href = `/chatbox?chat=${data.conversation_id}`;
				}

			} catch (error) {
				toastr.error('Failed to start conversation');
			}
		}

		async loadRecentMessages() {
			try {
				const recentContainer = document.getElementById('recent-messages');
				this.showContainerLoader(recentContainer);

				const response = await fetch('/chatbox/api/recent', {
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				const data = await response.json();
				this.renderRecentMessages(data.data, data.message);
			} catch (error) {
				//silent error
			}
		}

		renderRecentMessages(conversations, messages) {
			const container = document.querySelector('#recent-messages');
			if (!container) return;

			if (!conversations || conversations.length === 0) {
				container.innerHTML = '<div class="text-center p-3 text-muted">No messages yet</div>';
				return;
			}

			container.innerHTML = conversations.map((conv, index) => {
				const other = conv.user_one_id == this.userId ? conv.user_two : conv.user_one;
				const lastMsg = messages[index];

				if (!other) return '';

				const otherName = other.name;
				const otherAvatar = other.avatar;
				const messageContent = lastMsg?.content || 'No messages yet';
				const messageDate = lastMsg ? window.dateUtils(lastMsg.created_at, 'short-relative') : '';
				const hasUnread = conv.unread_count > 0;

				let displayContent = messageContent;
				if (lastMsg && lastMsg.sender_id == this.userId) {
					displayContent = `You: ${messageContent}`;
				}

				return `
					<a href="/chatbox?chat=${conv.id}" class="chat-dropdown-product d-flex align-products-center text-dark ${hasUnread ? 'has-unread' : ''}" data-user-id="${other.id}" data-dropdown-id="${conv.id}">
						<div class="flex-shrink-0 position-relative">
							<img src="${otherAvatar}" class="rounded-circle" width="40" height="40">
							<span class="position-absolute online-indicator" data-user-id="${other.id}"></span>
						</div>
						<div class="flex-grow-1 ms-2">
							<div class="chat-dropdown-title">${this.escapeHtml(otherName)}</div>
							<div class="d-flex align-products-center justify-content-between text-muted gap-3">
								<span class="small mb-0">
									${this.escapeHtml(this.truncateString(displayContent, 20))}
								</span>
								<span class="text-xsmall">${messageDate}</span>
							</div>
						</div>
					</a>
				`;
			}).join('');
		}

		toggleChatDropdown() {
			if (!this.chatDropdown) return;

			if (this.chatDropdown.classList.contains('show')) {
				this.closeChatDropdown();
			} else {
                if (typeof this.closeNotificationDropdown === 'function') {
                    this.closeNotificationDropdown();
                }
				this.openChatDropdown();
			}
		}

		openChatDropdown() {
			if (!this.chatDropdown) return;

			this.chatDropdown.classList.add('show');
			this.isOpen = true;
            if (this.chatDropdownMessages) {
                this.loadRecentMessages();
            }
		}
		closeChatDropdown() {
			if (!this.chatDropdown) return;

			this.chatDropdown.classList.remove('show');
			this.isOpen = false;
		}

		async loadConversations(reset = true) {
			try {
				const conversationList = document.getElementById('conversations-list');

				if (reset) {
                    this.currentPage = 1;
                    this.hasMorePages = true;
                    this.showContainerLoader(conversationList);
                }

				const response = await fetch(`/chatbox/api/conversations?page=${this.currentPage}`, {
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				const data = await response.json();

				if (reset) {
                    this.renderConversations(data.data, data.message);
                } else {
                    this.appendConversations(data.data, data.message);
                }

                this.hasMorePages = this.currentPage < data.last_page;

				setTimeout(() => this.updateUnreadMessageBadge(), 50);
			} catch (error) {
				toastr.error('Failed to load conversations');
			}
		}

        renderConversations(conversations, messages) {
			const container = document.querySelector('#conversations-list');
			if (!container) return;

			if (!conversations || conversations.length === 0) {
				container.innerHTML = '<div class="text-center p-4 text-muted">No conversations yet</div>';
				return;
			}

			const html = conversations.map((conv, index) => {
				const lastMsg = messages[index];
				return this.generateConversationMarkup(conv, lastMsg, index);
			}).filter(html => html !== '').join('');

			container.innerHTML = html;
		}

		async loadMoreConversations() {
			if (this.isLoadingMore || !this.hasMorePages) return;
			const containerE1 = document.getElementById('conversations-list');

			this.isLoadingMore = true;
			this.currentPage++;
			this.showLoadMoreLoader(containerE1, true);

			try {
				const response = await fetch(`/chatbox/api/conversations?page=${this.currentPage}`, {
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				const data = await response.json();

				this.appendConversations(data.data, data.message);
				this.hasMorePages = this.currentPage < data.last_page;

			} catch (error) {
				this.currentPage--;
				toastr.error('Failed to load more conversations');
			} finally {
				this.isLoadingMore = false;
				this.showLoadMoreLoader(containerE1, false);
			}
		}

		appendConversations(conversations, messages) {
			if (!conversations || conversations.length === 0) return;

			const container = document.querySelector('#conversations-list');
			if (!container) return;

			const newHtml = conversations.map((conv, index) => {
				const lastMsg = messages[index];
				return this.generateConversationMarkup(conv, lastMsg, index);
			}).filter(html => html !== '').join('');

			container.insertAdjacentHTML('beforeend', newHtml);
		}

		generateConversationMarkup(conv, lastMsg, index) {
			const other = conv.user_one_id == this.userId ? conv.user_two : conv.user_one;

			if (!other) return '';

			const otherName = other.name;
			const otherUsername = other.username;
			const otherAvatar = other.avatar;
			const isBlocked = other.is_blocked;
			const isBlockedByOther = other.is_blocked_by_other;
			const otherLastActive = other.is_last_active;

			const messageContent = lastMsg?.content || 'No messages';
			const messageDate = lastMsg ? window.dateUtils(lastMsg.created_at, 'short-relative') : '';
			const hasUnread = conv.unread_count > 0;

			let displayContent = messageContent;
			if (lastMsg && lastMsg.sender_id == this.userId) {
				displayContent = `You: ${messageContent}`;
			}

			const userDataStr = JSON.stringify({
				id: other.id,
				name: otherName,
				username: otherUsername,
				avatar: otherAvatar,
				is_blocked: isBlocked,
				is_blocked_by_other: isBlockedByOther,
				is_last_active: otherLastActive,
			});

			return `
				<div class="conversation-product ${hasUnread ? 'has-unread' : ''}" data-id="${conv.id}" data-user='${this.escapeHtml(userDataStr)}'>
					<div class="d-flex align-products-center">
						<div class="position-relative flex-shrink-0">
							<img src="${otherAvatar}" class="rounded-circle" width="40" height="40">
							<span class="position-absolute online-indicator" data-presence="${other.id}" style="display: none;"></span>
						</div>
						<div class="flex-grow-1 ms-2 overflow-hidden">
							<h6 class="mb-1">${this.escapeHtml(otherName)}</h6>
							<div class="d-flex align-products-center justify-content-between gap-1">
								<p class="mb-0 text-muted small text-truncate last-message-preview">${this.escapeHtml(this.truncateString(displayContent, 15))}</p>
								<span class="text-muted text-xsmall ${lastMsg ? 'conversation-timestamp' : ''}" data-original-timestamp="${lastMsg?.created_at}">${messageDate}</span>
							</div>
						</div>
					</div>
				</div>
			`;
		}

		async setConversationElement(conversationId) {
			document.querySelectorAll('.conversation-product').forEach(product => {
				product.classList.remove('active');
			});

			const conversationEl = document.querySelector(`[data-id="${conversationId}"]`);
			if (conversationEl) {
				conversationEl.classList.add('active');
			}

			try {
				await fetch(`/chatbox/conversation/${conversationId}/mark-read`, {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				if (conversationEl && conversationEl.classList.contains('has-unread')) {
					conversationEl.classList.remove('has-unread');
				}

				await this.updateUnreadMessageBadge();

				if (this.isChatDropdownEnabled) {
					await this.loadRecentMessages();
				}

			} catch (error) {
				//silent error
			}
		}

		async openConversation(conversationId, otherUser) {
		    const chatContainer = document.getElementById('chat-messages');
		    this.showContainerLoader(chatContainer);
			this.currentConversation = conversationId;
			this.currentRecipient = otherUser;

			if (window.location.pathname.includes('chatbox')) {
                const newUrl = `/chatbox?chat=${conversationId}`;
                window.history.pushState({conversationId}, '', newUrl);
            }

			this.setConversationElement(conversationId);

			try {
				const response = await fetch(`/chatbox/api/conversation/${conversationId}`, {
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});
				const data = await response.json();
				this.renderMessages(data.data);

				this.subscribeToConversation(conversationId);
				this.updateChatHeader(otherUser);

				document.querySelector('#chat-header')?.classList.remove('d-none');
				document.querySelector('#message-form-container')?.classList.remove('d-none');

			    this.updateBlockButton(otherUser);

			    if (otherUser.is_blocked) {
					this.showBlockedState();
				} else if (otherUser.is_blocked_by_other) {
                    this.showRestrictedState();
                } else {
					this.showNormalState();
				}

				if (window.innerWidth <= 767.98 && window.openMobileChat) {
					window.openMobileChat();
				}

			} catch (error) {
				toastr.error('Failed to load conversation');
			}
		}

		updateChatHeader(user) {
			const nameEl = document.getElementById('recipient-name');
			const avatarEl = document.getElementById('recipient-avatar');
			const isOnline = document.querySelector(`[data-presence="${user.id}"]`)?.style.display !== 'none';

			if (nameEl) nameEl.textContent = user.name || 'Unkown User';
			if (avatarEl) avatarEl.src = user.avatar;
			this.updateChatStatus(isOnline);
		}

		renderMessages(messages) {
			const container = document.querySelector('#chat-messages');
			if (!container) return;

			if (!messages || !Array.isArray(messages)) return;

			container.innerHTML = '';

			let lastDateString = null;
			let htmlParts = [];
			const TIME_GROUP_THRESHOLD = 5 * 60 * 1000;

			messages.forEach((msg, index) => {
				const msgDate = new Date(msg.created_at);
				const currentDateString = msgDate.toDateString();

				if (lastDateString !== currentDateString) {
					const dateHeaderHtml = `
						<div class="message-date-header position-relative border-bottom text-center">
							<span class="badge position-absolute bg-light text-muted fw-500 small rounded-pill border shadow-sm" style="top:-12px">
								${window.dateUtils(msg.created_at, 'date-header')}
							</span>
						</div>
					`;
					htmlParts.push(dateHeaderHtml);
					lastDateString = currentDateString;
				}

				const isOwn = msg.sender_id == this.userId;
				const prevMsg = messages[index - 1];
				const nextMsg = messages[index + 1];

				const shouldGroupWithPrevious = prevMsg &&
					prevMsg.sender_id === msg.sender_id &&
					(msgDate - new Date(prevMsg.created_at)) <= TIME_GROUP_THRESHOLD;

				const shouldGroupWithNext = nextMsg &&
					nextMsg.sender_id === msg.sender_id &&
					(new Date(nextMsg.created_at) - msgDate) <= TIME_GROUP_THRESHOLD;

				const isLastInGroup = !shouldGroupWithNext;

				let marginClass = '';
				let borderRadius = '';

				if (!shouldGroupWithPrevious && !shouldGroupWithNext) {
					marginClass = 'mb-1';
					borderRadius = '18px';
				} else if (!shouldGroupWithPrevious) {
					marginClass = 'mb-1';
					borderRadius = isOwn ? '18px 18px 5px 18px' : '18px 18px 18px 5px';
				} else if (isLastInGroup) {
					marginClass = 'mb-1';
					borderRadius = isOwn ? '18px 5px 18px 18px' : '5px 18px 18px 18px';
				} else {
					marginClass = 'mb-1';
					borderRadius = isOwn ? '18px 5px 5px 18px' : '5px 18px 18px 5px';
				}

				const messageHtml = `
					<div class="message-bubble ${marginClass} ${isOwn ? 'message-sent' : 'message-received'}"
						 id="message-${msg.id}"
						 data-timestamp="${msg.created_at}"
						 data-sender-id="${msg.sender_id}"
						 style="${isOwn ? 'margin-left: auto;' : ''}
								background: ${isOwn ? 'linear-gradient(135deg, #198754, #20c997)' : '#f1f3f4'};
								color: ${isOwn ? '#fff' : '#000'};
								border-radius: ${borderRadius};
								max-width: 70%;
								padding: 7px 12px;
								word-wrap: break-word;">
						<div>${this.escapeHtml(msg.content)}</div>
					</div>
				`;

				htmlParts.push(messageHtml);

				if (isLastInGroup) {
					const timestampHtml = `
						<div class="message-timestamp" style="text-align: ${isOwn ? 'right' : 'left'};">
							<small class="text-muted" style="font-size: 0.7rem;">${window.dateUtils(msg.created_at, 'time-only')}</small>
						</div>
					`;
					htmlParts.push(timestampHtml);
				}
			});

			container.innerHTML = htmlParts.join('');

			setTimeout(() => {
				if (window.scrollChatToBottom) {
					window.scrollChatToBottom(false);
				}
			}, 200);
		}

		subscribeToConversation(conversationId) {
			if (this.ably) {
				const channel = this.ably.channels.get(`conversation:${conversationId}`);
				channel.subscribe('new-message', (message) => {
					if (message.data.sender_id != this.userId) {
						this.addMessageToChat(message.data);
					}
				});
			}
		}

		addMessageToChat(data) {
			const container = document.querySelector('#chat-messages');
			if (!container) return;

			const message = data.message || data;
			if (!message || !message.id) return;

			const messageConversationId = message.conversation_id || message.messenger_model_id;
			if (messageConversationId != this.currentConversation) return;

			if (document.getElementById(`message-${message.id}`)) return;

			const msgDate = new Date(message.created_at);
			const TIME_GROUP_THRESHOLD = 5 * 60 * 1000; // 5 minutes
			const isOwn = Number(message.sender_id) === Number(this.userId);
			const lastMessageEl = container.querySelector('.message-bubble:last-child');
			let shouldGroupWithPrevious = false;
			let previousWasOwn = false;

			if (lastMessageEl) {
				const lastSenderId = lastMessageEl.getAttribute('data-sender-id');
				const lastTimestamp = lastMessageEl.getAttribute('data-timestamp');
				previousWasOwn = lastMessageEl.classList.contains('message-sent');

				if (lastSenderId && lastTimestamp) {
					const lastMsgDate = new Date(lastTimestamp);
					const timeDiff = msgDate - lastMsgDate;

					shouldGroupWithPrevious = (
						Number(message.sender_id) === Number(lastSenderId) &&
						timeDiff <= TIME_GROUP_THRESHOLD
					);
				}
			}

			if (!shouldGroupWithPrevious && lastMessageEl) {
				const lastTimestamp = lastMessageEl.getAttribute('data-timestamp');
				if (lastTimestamp) {
					const timestampDiv = document.createElement('div');
					timestampDiv.className = 'message-timestamp';
					timestampDiv.style.textAlign = previousWasOwn ? 'right' : 'left';
					timestampDiv.innerHTML = `<small class="text-muted" style="font-size: 0.7rem;">${window.dateUtils(lastTimestamp, 'time-only')}</small>`;
					container.appendChild(timestampDiv);
				}
			}

			let marginClass, borderRadius;

			if (!shouldGroupWithPrevious) {
				marginClass = 'mb-1';
				borderRadius = isOwn ? '18px 18px 5px 18px' : '18px 18px 18px 5px';
			} else {
				marginClass = 'mb-1';
				borderRadius = isOwn ? '18px 5px 5px 18px' : '5px 18px 18px 5px';

				if (lastMessageEl) {
					const wasPrevOwn = lastMessageEl.classList.contains('message-sent');
					lastMessageEl.style.borderRadius = wasPrevOwn ? '18px 5px 5px 18px' : '5px 18px 18px 5px';
				}
			}

			const messageDiv = document.createElement('div');
			messageDiv.className = `message-bubble ${marginClass} ${isOwn ? 'message-sent' : 'message-received'}`;
			messageDiv.id = `message-${message.id}`;
			messageDiv.setAttribute('data-timestamp', message.created_at);
			messageDiv.setAttribute('data-sender-id', message.sender_id);

			messageDiv.style.cssText = `
				background: ${isOwn ? 'linear-gradient(135deg, #198754, #20c997)' : '#f1f3f4'};
				color: ${isOwn ? '#fff' : '#000'};
				border-radius: ${borderRadius};
				max-width: 70%;
				padding: 7px 12px;
				word-wrap: break-word;
				${isOwn ? 'margin-left: auto;' : ''}
			`;

			messageDiv.innerHTML = `<div>${this.escapeHtml(message.content)}</div>`;

			container.appendChild(messageDiv);

			setTimeout(() => {
				if (window.scrollChatToBottom) {
					window.scrollChatToBottom();
				}
			}, 100);
		}

		async sendMessage() {
			const input = document.querySelector('#message-input');
			const content = input?.value?.trim();
			const currentDate = new Date().toISOString();

			if (!content || !this.currentRecipient) return;

			try {
				const response = await fetch('/chatbox', {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					},
					body: JSON.stringify({
						recipient_id: this.currentRecipient.id,
						content: content
					})
				});

				const data = await response.json();

				if (data.error) {
					toastr.error(data.error);
					return;
				}

				input.value = '';
				this.updateLastMessagePreview(
                    this.currentConversation,
                    content,
                    new Date().toISOString()
                );

			} catch (error) {
				toastr.error('Failed to send message');
			}
		}

		updateLastMessagePreview(conversationId, messageContent, newMessageTimestamp = null) {
			const conversationsContainer = document.getElementById('conversations-list');
			if (!conversationsContainer) return;

			const currentConvElement = conversationsContainer.querySelector(`[data-id="${conversationId}"]`);
			if (!currentConvElement) return;

			conversationsContainer.removeChild(currentConvElement);
			conversationsContainer.insertBefore(currentConvElement, conversationsContainer.firstChild);

			if (messageContent && messageContent.trim().length > 0) {
				if (!currentConvElement.classList.contains('active')) {
					currentConvElement.classList.add('has-unread');
				}

				const previewEl = currentConvElement.querySelector('.last-message-preview');
				if (previewEl) {
					previewEl.textContent = this.truncateString(messageContent, 15);
				}

				if (newMessageTimestamp) {
					const timestampEl = currentConvElement.querySelector('.conversation-timestamp');
					if (timestampEl) {
						timestampEl.setAttribute('data-original-timestamp', newMessageTimestamp);

						const messageTime = new Date(newMessageTimestamp);
						const now = new Date();
						const diffSeconds = Math.floor((now - messageTime) / 1000);
						if (diffSeconds < 60) {
							timestampEl.textContent = 'Just now';
						} else {
							timestampEl.textContent = window.dateUtils(newMessageTimestamp, 'shot-relative');
						}
					}
				}
			}
		}

		async updateUnreadMessageBadge() {
            if (!this.userId) return;

			try {
				const response = await fetch('/chatbox/unread-count', {
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				const data = await response.json();
				const badge = document.getElementById('unreadMessageBadge');

				if (badge) {
					const hasUnread = data.unread_count > 0;
					badge.textContent = hasUnread ? (data.unread_count > 9 ? '9+' : data.unread_count) : '';
					badge.classList.toggle('d-none', !hasUnread);
					badge.classList.toggle('has-unread', hasUnread);
				}

			} catch (error) {
				// silent error
			}
		}

		startConversationTimestampRefresh() {
			this.conversationTimestampInterval = setInterval(() => {
				const timestampElements = document.querySelectorAll('.conversation-timestamp');
				timestampElements.forEach(el => {
					const originalTimestamp = el.getAttribute('data-original-timestamp');
					if (originalTimestamp) {
						const relativeTime = window.dateUtils(originalTimestamp, 'short-relative');
						el.textContent = relativeTime || 'Just now';
					}
				});
			}, 60000);
		}

		initializeTypingIndicator() {
			const messageInput = document.getElementById('message-input');
			let typingTimer;
			let isTyping = false;

			if (!messageInput) return;

			messageInput.addEventListener('input', () => {
				if (!isTyping && messageInput.value.trim()) {
					isTyping = true;
					this.sendTypingStatus(true);
				}

				clearTimeout(typingTimer);
				typingTimer = setTimeout(() => {
					if (isTyping) {
						isTyping = false;
						this.sendTypingStatus(false);
					}
				}, 1000);
			});

			messageInput.addEventListener('blur', () => {
				if (isTyping) {
					isTyping = false;
					this.sendTypingStatus(false);
				}
			});
		}

		sendTypingStatus(typing) {
			if (this.ably && this.currentConversation) {
				const channel = this.ably.channels.get(`conversation:${this.currentConversation}`);
				channel.publish('typing', {
					user_id: this.userId,
					typing: typing
				});
			}
		}

		handleUnreadMessage(message, conversationId) {
			if (message.sender_id == this.userId) return;

			if (conversationId == this.currentConversation && document.visibilityState === 'visible') {
				return;
			}

			this.updateUnreadMessageBadge();

			if (this.isChatDropdownEnabled && this.chatDropdownMessages) {
				this.loadRecentMessages();
			}
		}

		handleNewMessage(messageData) {
		    const message = messageData.message;
            const conversationId = messageData.conversation_id;

            if (!message || !conversationId) {
                return;
            }

			if (this.isFullPageMessenger  && conversationId == this.currentConversation) {
				this.addMessageToChat(message);
			}

			this.updateLastMessagePreview(conversationId, message.content, message.created_at);
			this.handleUnreadMessage(message, conversationId);

			if (message.sender_id != this.userId) {
				this.playNotificationSound();
			}
		}

		initializePresence() {
			if (!this.ably) return;

			const presenceChannel = this.ably.channels.get('presence-users');

			presenceChannel.presence.enter({
				userId: this.userId,
				username: this.username
			});

			presenceChannel.presence.subscribe((msg) => {
				const userId = Number(msg.clientId);
				const isOnline = (msg.action === 'enter' || msg.action === 'update');
				this.updateUserOnlineStatus(userId, isOnline);
			});

			this.presenceUpdateInterval = setInterval(() => {
				if (document.visibilityState === 'visible') {
					presenceChannel.presence.update({
						userId: this.userId,
						username: this.username
					});
				}
			}, 30000);
		}

		updateUserOnlineStatus(userId, isOnline) {
			document.querySelectorAll(`[data-presence="${userId}"]`).forEach(el => {
				el.style.display = isOnline ? 'block' : 'none';
			});

			document.querySelectorAll(`.online-indicator[data-user-id="${userId}"]`).forEach(el => {
				el.style.display = isOnline ? 'block' : 'none';
			});

			if (this.currentRecipient && this.currentRecipient.id == userId) {
				this.updateChatStatus(isOnline);
			}
		}

		updateChatStatus(isOnline) {
			const statusEl = document.getElementById('recipient-status');
			if (!statusEl) return;

			if (isOnline) {
				statusEl.textContent = 'Online';
				statusEl.className = 'text-success small';
			} else {
				const lastActive = this.currentRecipient?.last_active_formatted || this.currentRecipient?.is_last_active;
				const relativeTime = window.dateUtils(lastActive, 'chat-header');
				statusEl.textContent = relativeTime || 'Long time ago';
				statusEl.className = 'text-muted small';
			}
		}

		startPresenceTimestampRefresh() {
			this.presenceTimestampRefreshInterval = setInterval(() => {
				if (this.currentRecipient) {
					const statusEl = document.getElementById('recipient-status');
					if (statusEl && statusEl.textContent !== 'Online') {
						const lastActive = this.currentRecipient?.last_active_formatted || this.currentRecipient.is_last_active;
						const relativeTime = window.dateUtils(lastActive, 'chat-header');
						statusEl.textContent = relativeTime || 'Long time ago';
					}
				}
			}, 60000);
		}

		toggleSound() {
			this.soundEnabled = !this.soundEnabled;
			localStorage.setItem('notificationSoundEnabled', this.soundEnabled);
			this.updateSoundButton();

			if (this.soundEnabled) {
				this.playNotificationSound();
			}
		}

		updateSoundButton() {
			if (!this.soundIcon || !this.soundToggleBtn) {
				return;
			}

			if (this.soundEnabled) {
				this.soundIcon.className = 'bi bi-volume-up';
				this.soundToggleBtn.classList.remove('sound-disabled');
				this.soundToggleBtn.classList.add('sound-enabled');
				this.soundToggleBtn.querySelector('.sound-toggle-text').textContent = 'Mute notification sound';
			} else {
				this.soundIcon.className = 'bi bi-volume-mute';
				this.soundToggleBtn.classList.remove('sound-enabled');
				this.soundToggleBtn.classList.add('sound-disabled');
				this.soundToggleBtn.querySelector('.sound-toggle-text').textContent = 'Unmute notification sound';
			}
		}

		playNotificationSound() {
			if (!this.soundEnabled) {
				return;
			}

			try {
				const audio = new Audio(this.soundPath);
				audio.volume = 0.9;
				audio.play().catch(() => {
					// Handle sound play error silently
				});
			} catch (error) {
				// Handle sound not available error silently
			}
		}

		// Notification methods
		updateBadge(count) {
			this.unreadCount = count;

			if (count > 0) {
				if (this.notificationBadge) {
					this.notificationBadge.textContent = count > 9 ? '9+' : count;
					this.notificationBadge.classList.remove('d-none');
					this.notificationBadge.classList.add('has-unread');
				}

				if (this.bellIcon) {
					this.bellIcon.classList.add('has-notifications');
				}

				if (this.headerUnreadNotifCount) {
					this.headerUnreadNotifCount.textContent = `(${count > 99 ? '99+' : count})`;
					this.headerUnreadNotifCount.classList.remove('d-none');
				}
			} else {
				if (this.notificationBadge) {
					this.notificationBadge.classList.add('d-none');
					this.notificationBadge.classList.remove('has-unread');
				}

				if (this.bellIcon) {
					this.bellIcon.classList.remove('has-notifications');
				}

				if (this.headerUnreadNotifCount) {
					this.headerUnreadNotifCount.classList.add('d-none');
				}
			}
		}

		async loadNotifications() {
			if (!this.isNotificationPageEnabled) {
				return;
			}

			try {
				this.showContainerLoader(this.notificationList);

				const response = await fetch(`/${this.username}/notifications/unread-count`, {
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			// Get response as text first to strip BOM if present
			let responseText = await response.text();
			// Remove BOM (EF BB BF) if present
			if (responseText.charCodeAt(0) === 0xFEFF) {
				responseText = responseText.substring(1);
			}
			const data = JSON.parse(responseText);

			this.updateBadge(data.count);
			await this.loadRecentNotifications();

		} catch (error) {
			this.showError('Error loading notifications');
		}
	}

		async loadRecentNotifications() {
			if (!this.isNotificationPageEnabled) {
				return;
			}

			try {
				const response = await fetch(`/${this.username}/notifications/recent?limit=10`, {
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			// Get response as text first to strip BOM if present
			let responseText = await response.text();
			// Remove BOM (EF BB BF) if present
			if (responseText.charCodeAt(0) === 0xFEFF) {
				responseText = responseText.substring(1);
			}
			const data = JSON.parse(responseText);

			this.notifications = data.notifications || [];
			this.renderNotifications();

		} catch (error) {
			this.showError('Error loading recent notifications');
		}
	}

		toggleNotificationDropdown() {
			if (!this.notificationDropdown) return;

			if (this.notificationDropdown.classList.contains('show')) {
				this.closeNotificationDropdown();
			} else {
                if (typeof this.closeChatDropdown === 'function') {
                    this.closeChatDropdown();
                }
				this.openNotificationDropdown();
			}
		}

		openNotificationDropdown() {
			if (!this.notificationDropdown) return;

			this.notificationDropdown.classList.add('show');
			this.isOpen = true;
            if (this.notificationList) {
			    this.loadNotifications();
            }
		}

		closeNotificationDropdown() {
			if (!this.notificationDropdown) return;

			this.notificationDropdown.classList.remove('show');
			this.isOpen = false;
		}

		renderNotifications() {
			if (!this.notificationList) return;

			if (this.notifications.length === 0) {
				this.showEmpty();
				return;
			}

			const html = this.notifications.map(notification => {
				const data = notification.data || {};
				const isUnread = !notification.read_at;
				const notficationDate = new Date(notification.created_at);
				const timeAgo = window.dateUtils(notficationDate, 'short-relative');
				const hasActionUrl = data.action_url && data.action_url.trim() !== '';
				const hasImage = data.preview_image;

				return `
					<div class="notification-product ${isUnread ? 'unread' : ''} ${hasActionUrl ? 'clickable' : ''}"
						 data-id="${notification.id}"
						 ${hasActionUrl ? `data-action-url="${data.action_url}"` : ''}
						 onclick="BroadcastManager.handleNotificationClick('${notification.id}', '${hasActionUrl ? data.action_url : ''}')">
						<div class="notification-content">
							<div class="notification-icon ${data.color || 'info'}">
								${hasImage ? `<img src="${hasImage}" alt="thumbnail" />` : `<i class="bi bi-${data.icon || 'bell'}"></i>`}
							</div>
							<div class="notification-text">
								<div class="notification-title">${this.escapeHtml(data.title || 'Notification')}</div>
								<div class="notification-message">${this.escapeHtml(data.message || '')}</div>
								<div class="notification-time">${timeAgo}</div>
							</div>
						</div>
					</div>
				`;
			}).join('');

			this.notificationList.innerHTML = html;

			const hasUnread = this.notifications.some(n => !n.read_at);
			if (this.markAllReadNotifBtn) {
			    if (hasUnread) {
			        this.markAllReadNotifBtn.classList.remove('d-none');
			    } else {
			        this.markAllReadNotifBtn.classList.add('d-none');
			    }
			}
		}

		handleNotificationClick(notificationId, actionUrl) {
			this.markAsRead(notificationId);

			if (actionUrl && actionUrl.trim() !== '' && actionUrl !== '') {
				setTimeout(() => {
					window.location.href = actionUrl;
				}, 100);
			}
		}

		showEmpty() {
			if (!this.notificationList) return;

			this.notificationList.innerHTML = `
				<div class="notification-empty">
					<i class="bi bi-bell"></i>
					<div>No notifications found!</div>
				</div>
			`;

			if (this.markAllReadNotifBtn) {
				this.markAllReadNotifBtn.classList.add('d-none');
			}
		}

		showError(message) {
			if (!this.notificationList) return;

			this.notificationList.innerHTML = `
				<div class="notification-empty">
					<i class="bi bi-exclamation-triangle"></i>
					<div>${message}</div>
				</div>
			`;
		}

		async markAsRead(notificationId) {
			try {
				const response = await fetch(`/${this.username}/notifications/${notificationId}/read`, {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				if (response.ok) {
					const product = document.querySelector(`[data-id="${notificationId}"]`);
					if (product) {
						product.classList.remove('unread');
					}

					this.updateBadge(Math.max(0, this.unreadCount - 1));
					this.loadRecentNotifications();
				}
			} catch (error) {
				this.showError('Error marking notification as read');
			}
		}

		async markAllAsRead() {
			try {
				const response = await fetch(`/${this.username}/notifications/mark-all-read`, {
					method: 'POST',
					headers: {
						'Accept': 'application/json',
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': this.csrfToken
					}
				});

				if (response.ok) {
					document.querySelectorAll('.notification-product.unread').forEach(product => {
						product.classList.remove('unread');
					});

					this.updateBadge(0);

					if (this.markAllReadNotifBtn) {
						this.markAllReadNotifBtn.classList.add('d-none');
					}
				}
			} catch (error) {
				this.showError('Error marking all notifications as read');
			}
		}

		showPushNotification(notification) {
			if (!('Notification' in window) || Notification.permission !== 'granted') {
				return;
			}

			try {
				const data = notification.data || {};
				const pushNotification = data.push_notification || {};
				const favicon = '/themes/basic/images/favicon.png';

				const options = {
					body: pushNotification.body || data.message || '',
					icon: pushNotification.icon || favicon,
					badge: pushNotification.badge || favicon,
					tag: pushNotification.tag || `notification-${notification.id}`,
					requireInteraction: pushNotification.requireInteraction || false,
					silent: pushNotification.silent || false,
					data: pushNotification.data || {
						url: data.action_url || '/',
						type: data.type || 'notification',
						notification_id: notification.id
					},
				};

				const pushAlert = new Notification(
					pushNotification.title || data.title || 'New Notification',
					options
				);

				const handleNotificationClick = (event) => {
					if (event.preventDefault) {
						event.preventDefault();
					}

					if (window.focus) {
						window.focus();
					}

					let actionUrl = null;

					if (event.target && event.target.data && event.target.data.url) {
						actionUrl = event.target.data.url;
					} else if (data.action_url) {
						actionUrl = data.action_url;
					} else if (pushNotification.data && pushNotification.data.url) {
						actionUrl = pushNotification.data.url;
					}

					if (actionUrl && actionUrl !== '/' && actionUrl !== '') {
						setTimeout(() => {
							try {
								if (actionUrl.startsWith('http') || actionUrl.startsWith('//')) {
									const newWindow = window.open(actionUrl, '_blank');
									if (newWindow) {
										newWindow.focus();
									}
								} else {
									window.location.href = actionUrl;
								}
							} catch (error) {
								window.location.href = actionUrl;
							}
						}, 150);
					}

					if (notification.id) {
						this.markAsRead(notification.id);
					}

					pushAlert.close();
				};

				if (pushAlert.addEventListener) {
					pushAlert.addEventListener('click', handleNotificationClick);
				} else {
					pushAlert.onclick = handleNotificationClick;
				}

			} catch (error) {
				// Silent error handling
			}
		}

		addNotification(notification) {
			this.notifications.unshift(notification);

			if (this.notifications.length > 10) {
				this.notifications = this.notifications.slice(0, 10);
			}

			this.updateBadge(this.unreadCount + 1);

			if (this.isOpen) {
				this.renderNotifications();
			}

			if (notification.data && notification.data.show_push) {
				this.showPushNotification(notification);
			}

			this.playNotificationSound();
		}

		initializeAbly() {
			if (typeof Ably !== 'undefined' && this.ablyKey) {
				try {
					const channelName = `private:user-${this.username}`;
					this.ably = new Ably.Realtime({
						authCallback: (tokenParams, callback) => {
							fetch('/ably/auth', {
								method: 'POST',
								headers: {
									'X-CSRF-TOKEN': this.csrfToken,
									'Accept': 'application/json',
									'Content-Type': 'application/json',
								},
								credentials: 'include',
								body: JSON.stringify({
									channel: channelName,
									tokenParams: tokenParams
								})
							})
							.then(response => response.json())
							.then(tokenRequest => callback(null, tokenRequest))
							.catch(error => callback(error, null));
						}
					});

					this.ably.connection.on('connected', () => {
						const channel = this.ably.channels.get(channelName);

						channel.subscribe('notification', (message) => {
							try {
								let data = message.data;

								if (typeof data === 'string') {
									data = this.safeJSONParse(data, {});
								}

								this.addNotification({
									id: Date.now(),
									data: data,
									read_at: null,
									created_at: new Date().toISOString()
								});

							} catch (error) {
								// Silent error handling
							}
						});

						const messengerChannel = this.ably.channels.get(`user-${this.userId}`);
						messengerChannel.subscribe('new-message', (message) => {
							this.handleNewMessage(message.data);
						});

						this.initializePresence();
					});

					this.ably.connection.on('failed', () => {
						setTimeout(() => this.initializeAbly(), 30000);
					});

				} catch (error) {
					setTimeout(() => this.initializeAbly(), 30000);
				}
			}
		}

		startPolling() {
			setInterval(() => {
				if (!this.isOpen && this.isNotificationPageEnabled) {
					this.loadNotifications();
				}
			}, 30000);
		}

		requestNotificationPermission() {
			if ('Notification' in window && Notification.permission === 'default') {
				Notification.requestPermission().then(permission => {
					// Handle permission response if needed
				});
			}
		}


		cleanup() {
			if (this.presenceUpdateInterval) {
				clearInterval(this.presenceUpdateInterval);
				this.presenceUpdateInterval = null;
			}

			if (this.presenceTimestampRefreshInterval) {
				clearInterval(this.presenceTimestampRefreshInterval);
				this.presenceTimestampRefreshInterval = null;
			}

			if (this.conversationTimestampInterval) {
				clearInterval(this.conversationTimestampInterval);
				this.conversationTimestampInterval = null;
			}

			if (this.ably) {
				const presenceChannel = this.ably.channels.get('presence-users');
				presenceChannel.presence.leave();
			}
		}

		registerCleanupEvents() {
			window.addEventListener('beforeunload', () => this.cleanup());
			window.addEventListener('unload', () => this.cleanup());

			document.addEventListener('visibilitychange', () => {
				if (document.visibilityState === 'hidden') {
					this.cleanup();
				}
			});
		}
	}

	$(document).ready(function() {
		window.BroadcastManager = new BroadcastManager();

		const chatIcon = document.getElementById('chatIcon');
		if (chatIcon){
		    window.BroadcastManager.updateUnreadMessageBadge();
		}

		$(document).one('click', function() {
			window.BroadcastManager.requestNotificationPermission();
		});
	});

})(jQuery);

