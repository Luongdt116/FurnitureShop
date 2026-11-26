import type { ChatMessage, ChatProduct } from './storage';

export interface WebhookRequest {
  message: string;
  timestamp: string;
  sessionId: string;
  context: {
    url: string;
  };
  history?: ChatMessage[];
}

export interface WebhookResponse {
  response: string;
  products?: ChatProduct[];
  suggestedQuestions?: string[];
}

export class WebhookService {
  private readonly url: string;
  private controller: AbortController | null = null;

  constructor(webhookUrl: string) {
    this.url = webhookUrl;
  }

  public async sendMessage(request: WebhookRequest): Promise<WebhookResponse> {
    // Cancel any existing request
    if (this.controller) {
      this.controller.abort();
    }

    this.controller = new AbortController();

    try {
      const webhookUrl = new URL(this.url, window.location.origin);
      if (webhookUrl.origin !== window.location.origin) {
        throw new Error('Chat webhook must use this website domain.');
      }

      const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
      const response = await fetch(webhookUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
        body: JSON.stringify(request),
        credentials: 'same-origin',
        signal: this.controller.signal,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      this.controller = null;
      return data;
    } catch (error) {
      if (error instanceof Error && error.name === 'AbortError') {
        throw new Error('Request cancelled');
      }
      throw error;
    }
  }

  public cancelRequest(): void {
    if (this.controller) {
      this.controller.abort();
      this.controller = null;
    }
  }
}
