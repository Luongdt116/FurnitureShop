import { BaseComponent } from './base-component';
import { StorageService, ChatMessage } from '../services/storage';
import { WebhookService } from '../services/webhook';

export class ChatWindow extends BaseComponent {
  static get observedAttributes() {
    return ['webhook-url', 'title', 'welcome-message', 'history-enabled', 'history-clear-button', 'position'];
  }

  private styles = `
    .chat-window {
      position: absolute;
      bottom: 80px;
      right: 0;
      width: 320px;
      height: 480px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.2s ease, transform 0.2s ease;
      pointer-events: none;
    }

    .chat-window.open {
      opacity: 1;
      transform: translateY(0);
      pointer-events: all;
    }

    :host([position="bottom-left"]) .chat-window {
      right: auto;
      left: 0;
    }

    .header {
      padding: 16px;
      background-color: var(--theme-color, #1E40AF);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h2 {
      margin: 0;
      font-size: 16px;
      font-weight: 500;
    }

    .header-actions {
      display: flex;
      gap: 8px;
    }

    .header button {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      padding: 4px;
      opacity: 0.8;
      transition: opacity 0.2s;
    }

    .header button:hover {
      opacity: 1;
    }

    .messages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .message {
      max-width: 80%;
      padding: 8px 12px;
      border-radius: 12px;
      font-size: 14px;
      line-height: 1.4;
    }

    .message.user {
      align-self: flex-end;
      background-color: var(--theme-color, #1E40AF);
      color: white;
    }

    .message.backend {
      align-self: flex-start;
      background-color: #f0f0f0;
      color: #333;
    }

    .message.system {
      align-self: center;
      background-color: #f5f5f5;
      color: #666;
      font-style: italic;
      font-size: 13px;
    }

    .message.error {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .message-status {
      font-size: 11px;
      margin-top: 4px;
      opacity: 0.8;
    }

    .cancel-button {
      font-size: 12px;
      padding: 2px 6px;
      background: rgba(0, 0, 0, 0.1);
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-left: 8px;
    }

    .product-list {
      display: grid;
      gap: 10px;
      margin-top: 10px;
      width: min(290px, 100%);
    }

    .product-card {
      overflow: hidden;
      border: 1px solid #e7e1d9;
      border-radius: 12px;
      background: #fffdf9;
      color: #1c1917;
      font-style: normal;
    }

    .product-card img,
    .product-image-placeholder {
      display: flex;
      width: 100%;
      height: 140px;
      object-fit: cover;
      align-items: center;
      justify-content: center;
      background: #f4efe7;
      color: #766c60;
      font-size: 12px;
    }

    .product-card-body { padding: 10px 12px 12px; }
    .product-card-name { margin: 0 0 6px; font-size: 14px; font-weight: 700; }
    .product-card-price { color: #815e2a; font-size: 14px; font-weight: 700; }
    .product-card-compare { margin-left: 6px; color: #8a8178; font-size: 12px; text-decoration: line-through; }
    .product-card-attributes { margin: 7px 0 10px; color: #625a52; font-size: 12px; line-height: 1.45; }
    .product-card-link {
      display: inline-flex;
      padding: 7px 11px;
      border-radius: 7px;
      background: #1c1917;
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
    }

    .suggested-questions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 9px; }
    .suggested-question {
      padding: 5px 8px;
      border: 1px solid #d8c7a6;
      border-radius: 999px;
      background: #fffaf0;
      color: #5c482d;
      cursor: pointer;
      font-size: 11px;
    }

    .input-area {
      padding: 16px;
      border-top: 1px solid #eee;
      display: flex;
      gap: 8px;
    }

    .input-area input {
      flex: 1;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 20px;
      font-size: 14px;
    }

    .input-area button {
      background-color: var(--theme-color, #1E40AF);
      color: white;
      border: none;
      border-radius: 20px;
      padding: 8px 16px;
      cursor: pointer;
      font-size: 14px;
      transition: opacity 0.2s;
    }

    .input-area button:hover {
      opacity: 0.9;
    }

    .input-area button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  `;

  private isOpen = false;
  private storage: StorageService;
  private webhook: WebhookService;
  private sessionId: string;
  private hasShownWelcomeMessage = false;

  constructor() {
    super();
    const webhookUrl = this.getAttribute('webhook-url') || '';
    this.storage = new StorageService(webhookUrl);
    this.webhook = new WebhookService(webhookUrl);
    this.sessionId = this.storage.getSessionId();
    this.addStyles(this.styles);
    this.render();
  }

  protected render(): void {
    const window = this.createElement('div', 'chat-window');
    
    // Header
    const header = this.createElement('div', 'header');
    const title = this.createElement('h2', '', this.getAttribute('title') || 'Chat with us');
    const headerActions = this.createElement('div', 'header-actions');
    
    if (this.getAttribute('history-enabled') !== 'false' && 
        this.getAttribute('history-clear-button') !== 'false') {
      const clearBtn = this.createElement('button', '', '🗑️');
      clearBtn.type = 'button';
      clearBtn.title = 'Xóa lịch sử chat';
      clearBtn.setAttribute('aria-label', 'Xóa lịch sử chat');
      clearBtn.addEventListener('click', () => this.clearHistory());
      headerActions.appendChild(clearBtn);
    }
    
    const closeBtn = this.createElement('button', '', '✕');
    closeBtn.type = 'button';
    closeBtn.title = 'Đóng cửa sổ chat';
    closeBtn.setAttribute('aria-label', 'Đóng cửa sổ chat');
    closeBtn.addEventListener('click', () => this.close());
    headerActions.appendChild(closeBtn);
    
    header.appendChild(title);
    header.appendChild(headerActions);
    
    // Messages area
    const messages = this.createElement('div', 'messages');
    
    // Load existing messages
    if (this.getAttribute('history-enabled') !== 'false') {
      const existingMessages = this.storage.getMessages();
      existingMessages.forEach(msg => this.renderMessage(msg, messages));
      if (existingMessages.length > 0) this.hasShownWelcomeMessage = true;
    }
    
    // Input area
    const inputArea = this.createElement('div', 'input-area');
    const input = this.createElement('input') as HTMLInputElement;
    input.type = 'text';
    input.placeholder = 'Nhập câu hỏi về nội thất...';
    input.setAttribute('aria-label', 'Câu hỏi tư vấn');
    
    const sendBtn = this.createElement('button', '', 'Gửi');
    sendBtn.type = 'button';
    const handleSend = () => {
      const text = input.value.trim();
      if (text) {
        this.sendMessage(text);
        input.value = '';
      }
    };
    
    sendBtn.addEventListener('click', handleSend);
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleSend();
      }
    });
    
    inputArea.appendChild(input);
    inputArea.appendChild(sendBtn);
    
    window.appendChild(header);
    window.appendChild(messages);
    window.appendChild(inputArea);
    
    this.shadow.replaceChildren();
    this.shadow.appendChild(window);

    // Show welcome message only if it hasn't been shown yet and the chat is being opened
    const welcomeMessage = this.getAttribute('welcome-message');
    if (welcomeMessage && !this.hasShownWelcomeMessage && this.isOpen) {
      this.addSystemMessage(welcomeMessage);
      this.hasShownWelcomeMessage = true;
    }
  }

  private async sendMessage(text: string) {
    const messageId = crypto.randomUUID();
    const timestamp = new Date().toISOString();
    
    const message: ChatMessage = {
      id: messageId,
      text,
      sender: 'user',
      timestamp,
      status: 'sending'
    };

    // Add message to UI and storage
    this.storage.addMessage(message);
    this.renderMessage(message);

    try {
      // Send to webhook
      const response = await this.webhook.sendMessage({
        message: text,
        timestamp,
        sessionId: this.sessionId,
        context: {
          url: window.location.href
        },
        history: this.storage.getMessages().slice(-10) // Send last 10 messages
      });

      // Update message status
      message.status = 'sent';
      this.storage.updateMessage(messageId, { status: 'sent' });
      this.updateMessageStatus(messageId, 'sent');

      // Add response message
      const responseMessage: ChatMessage = {
        id: crypto.randomUUID(),
        text: response.response,
        sender: 'backend',
        timestamp: new Date().toISOString(),
        products: response.products ?? [],
        suggestedQuestions: response.suggestedQuestions ?? [],
      };
      
      this.storage.addMessage(responseMessage);
      this.renderMessage(responseMessage);

    } catch (error) {
      if (error instanceof Error && error.message === 'Request cancelled') {
        message.status = 'cancelled';
        this.storage.updateMessage(messageId, { status: 'cancelled' });
        this.updateMessageStatus(messageId, 'cancelled');
      } else {
        message.status = 'error';
        this.storage.updateMessage(messageId, { status: 'error' });
        this.updateMessageStatus(messageId, 'error');
        this.addSystemMessage('Chưa gửi được tin nhắn. Bạn thử lại sau nhé.');
      }
    }
  }

  private renderMessage(message: ChatMessage, container?: HTMLElement) {
    const messages = container || this.shadow.querySelector('.messages');
    if (!messages) return;

    const messageEl = this.createElement('div', `message ${message.sender}`);
    messageEl.textContent = message.text;
    messageEl.dataset.messageId = message.id;

    if (message.sender === 'user' && message.status) {
      const statusEl = this.createElement('div', 'message-status');
      statusEl.textContent = this.statusLabel(message.status);
      
      if (message.status === 'sending') {
        const cancelBtn = this.createElement('button', 'cancel-button', 'Cancel');
        cancelBtn.textContent = 'Hủy gửi';
        cancelBtn.type = 'button';
        cancelBtn.addEventListener('click', () => {
          this.webhook.cancelRequest();
        });
        statusEl.appendChild(cancelBtn);
      }
      
      messageEl.appendChild(statusEl);
    }

    if (message.products?.length) {
      const productList = this.createElement('div', 'product-list');
      message.products.slice(0, 5).forEach((product) => {
        const detailUrl = this.sameOriginUrl(product.url);
        if (!detailUrl) return;

        const card = this.createElement('article', 'product-card');
        if (product.imageUrl) {
          const imageUrl = this.sameOriginUrl(product.imageUrl);
          if (imageUrl) {
            const image = this.createElement('img') as HTMLImageElement;
            image.src = imageUrl;
            image.alt = product.name;
            image.loading = 'lazy';
            card.appendChild(image);
          } else {
            card.appendChild(this.createElement('div', 'product-image-placeholder', 'Ảnh sản phẩm chưa có'));
          }
        } else {
          card.appendChild(this.createElement('div', 'product-image-placeholder', 'Ảnh sản phẩm chưa có'));
        }

        const body = this.createElement('div', 'product-card-body');
        body.appendChild(this.createElement('h3', 'product-card-name', product.name));

        const priceRow = this.createElement('div', 'product-card-price', this.formatPrice(product.price));
        if (typeof product.compareAtPrice === 'number' && product.compareAtPrice > product.price) {
          priceRow.appendChild(this.createElement('span', 'product-card-compare', this.formatPrice(product.compareAtPrice)));
        }
        body.appendChild(priceRow);

        const attributeLines = Object.entries(product.attributes ?? {})
          .filter(([key, value]) => key !== 'stock' && value !== '')
          .map(([key, value]) => `${this.attributeLabel(key)}: ${String(value)}`);
        if (product.attributes?.stock !== undefined) {
          attributeLines.push(`Còn hàng: ${product.attributes.stock}`);
        }
        if (attributeLines.length) {
          body.appendChild(this.createElement('p', 'product-card-attributes', attributeLines.join(' · ')));
        }

        const link = this.createElement('a', 'product-card-link', 'Xem chi tiết');
        link.href = detailUrl;
        body.appendChild(link);
        card.appendChild(body);
        productList.appendChild(card);
      });
      if (productList.childElementCount) messageEl.appendChild(productList);
    }

    if (message.suggestedQuestions?.length) {
      const questions = this.createElement('div', 'suggested-questions');
      message.suggestedQuestions.slice(0, 4).forEach((question) => {
        const button = this.createElement('button', 'suggested-question', question);
        button.type = 'button';
        button.addEventListener('click', () => this.sendMessage(question));
        questions.appendChild(button);
      });
      messageEl.appendChild(questions);
    }

    messages.appendChild(messageEl);
    messages.scrollTop = messages.scrollHeight;
  }

  private sameOriginUrl(value: string): string | null {
    try {
      const url = new URL(value, window.location.origin);
      if (url.origin !== window.location.origin || !['http:', 'https:'].includes(url.protocol)) return null;
      return url.href;
    } catch {
      return null;
    }
  }

  private formatPrice(price: number): string {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(price);
  }

  private attributeLabel(key: string): string {
    const labels: Record<string, string> = {
      material: 'Chất liệu',
      dimensions: 'Kích thước',
      color: 'Màu sắc',
      warranty: 'Bảo hành',
    };
    return labels[key] ?? key;
  }

  private statusLabel(status: NonNullable<ChatMessage['status']>): string {
    const labels: Record<NonNullable<ChatMessage['status']>, string> = {
      sending: 'Đang gửi',
      sent: 'Đã gửi',
      error: 'Lỗi gửi',
      cancelled: 'Đã hủy',
    };
    return labels[status];
  }

  private updateMessageStatus(messageId: string, status: string) {
    const messageEl = this.shadow.querySelector(`[data-message-id="${messageId}"]`);
    if (messageEl) {
      const statusEl = messageEl.querySelector('.message-status');
      if (statusEl) {
        statusEl.textContent = this.statusLabel(status as NonNullable<ChatMessage['status']>);
      }
    }
  }

  private clearHistory() {
    if (confirm('Bạn có chắc muốn xóa lịch sử trò chuyện này không?')) {
      this.storage.clearHistory();
      const messages = this.shadow.querySelector('.messages');
      if (messages) {
        messages.replaceChildren();
      }
      this.sessionId = this.storage.getSessionId();
      // Reset welcome message flag when history is cleared
      this.hasShownWelcomeMessage = false;
    }
  }

  private addSystemMessage(text: string) {
    const message: ChatMessage = {
      id: crypto.randomUUID(),
      text,
      sender: 'system',
      timestamp: new Date().toISOString()
    };
    
    if (this.getAttribute('history-enabled') !== 'false') {
      this.storage.addMessage(message);
    }
    
    this.renderMessage(message);
  }

  private close() {
    this.isOpen = false;
    this.updateVisibility();
    this.dispatchEvent(new CustomEvent('close'));
  }

  public setOpen(open: boolean) {
    this.isOpen = open;
    this.updateVisibility();
    
    // If opening the chat and no messages exist, show welcome message
    if (open && !this.hasShownWelcomeMessage) {
      const welcomeMessage = this.getAttribute('welcome-message');
      if (welcomeMessage) {
        this.addSystemMessage(welcomeMessage);
        this.hasShownWelcomeMessage = true;
      }
    }
  }

  private updateVisibility() {
    const window = this.shadow.querySelector('.chat-window');
    if (window) {
      if (this.isOpen) {
        window.classList.add('open');
      } else {
        window.classList.remove('open');
      }
    }
  }

  attributeChangedCallback(name: string, oldValue: string, newValue: string) {
    if (oldValue !== newValue) {
      if (name === 'webhook-url') {
        const webhookUrl = newValue || '';
        this.storage = new StorageService(webhookUrl);
        this.webhook = new WebhookService(webhookUrl);
        this.sessionId = this.storage.getSessionId();
      }
      // Re-render to update all attributes
      this.render();
    }
  }
}

customElements.define('chat-window', ChatWindow);
