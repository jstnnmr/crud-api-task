import api from './client';

export const notesApi = {
  list: () => api.get('/notes'),
  show: (id) => api.get(`/notes/${id}`),
  store: (data) => api.post('/notes', data),
  update: (id, data) => api.put(`/notes/${id}`, data),
  destroy: (id) => api.delete(`/notes/${id}`),
  invite: (id, data) => api.post(`/notes/${id}/invite`, data),
  removeCollaborator: (noteId, collabId) =>
    api.delete(`/notes/${noteId}/collaborators/${collabId}`),
};
