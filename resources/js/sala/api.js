const json = async (url, opts = {}) => {
    const res = await fetch(url, { headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, ...opts });
    if (!res.ok) throw new Error(`${res.status} ${url}`);
    return res.json();
};

export const api = {
    program: () => json('/api/program'),
    messages: (programId, params = {}) => json(`/api/programs/${programId}/messages?` + new URLSearchParams(params)),
    highlights: (programId) => json(`/api/programs/${programId}/highlights`),
    highlight: (messageId) => json(`/api/messages/${messageId}/highlight`, { method: 'POST' }),
    next: (programId) => json(`/api/programs/${programId}/highlights/next`, { method: 'POST' }),
    updateHighlight: (id, data) => json(`/api/highlights/${id}`, { method: 'PATCH', body: JSON.stringify(data) }),
    feedback: (messageId, bad) => json(`/api/messages/${messageId}/feedback`, { method: 'POST', body: JSON.stringify({ bad_transcript: bad }) }),
};
