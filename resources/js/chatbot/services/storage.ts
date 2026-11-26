export interface ChatProduct {
  id: number;
  name: string;
  imageUrl: string | null;
  price: number;
  compareAtPrice: number | null;
  slug: string;
  url: string;
  attributes?: Record<string, string | number>;
}

export interface ChatMessage {
  id: string;
  text: string;
  sender: 'user' | 'backend' | 'system';
  timestamp: string;
  status?: 'sending' | 'sent' | 'error' | 'cancelled';
  products?: ChatProduct[];
  suggestedQuestions?: string[];
}

export class StorageService {
  private readonly storageKey: string;
  private readonly sessionKey: string;

  constructor(webhookUrl: string) {
    // Use webhookUrl as part of the storage key to separate conversations
    this.storageKey = `chat_history_${btoa(webhookUrl)}`;
    this.sessionKey = `${this.storageKey}_session`;
  }

  public getSessionId(): string {
    try {
      const existing = localStorage.getItem(this.sessionKey);
      if (existing) return existing;
      const sessionId = crypto.randomUUID();
      localStorage.setItem(this.sessionKey, sessionId);
      return sessionId;
    } catch {
      return crypto.randomUUID();
    }
  }

  public getMessages(): ChatMessage[] {
    try {
      const stored = localStorage.getItem(this.storageKey);
      return stored ? JSON.parse(stored) : [];
    } catch (e) {
      console.error('Failed to load chat history:', e);
      return [];
    }
  }

  public addMessage(message: ChatMessage): void {
    try {
      const messages = this.getMessages();
      messages.push(message);
      localStorage.setItem(this.storageKey, JSON.stringify(messages));
    } catch (e) {
      console.error('Failed to save message:', e);
    }
  }

  public updateMessage(id: string, updates: Partial<ChatMessage>): void {
    try {
      const messages = this.getMessages();
      const index = messages.findIndex(m => m.id === id);
      if (index !== -1) {
        messages[index] = { ...messages[index], ...updates };
        localStorage.setItem(this.storageKey, JSON.stringify(messages));
      }
    } catch (e) {
      console.error('Failed to update message:', e);
    }
  }

  public clearHistory(): void {
    try {
      localStorage.removeItem(this.storageKey);
      localStorage.removeItem(this.sessionKey);
    } catch (e) {
      console.error('Failed to clear history:', e);
    }
  }
}
