export interface AiMessage {
    id: number;
    session_id: string | null;
    user_id: number | null;
    role: 'user' | 'assistant' | 'system';
    content: string;
    meta: { usage?: { total_tokens?: number }; model?: string } | null;
    created_at: string;
}
