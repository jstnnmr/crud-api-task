import api from './client';

export const subjectsApi = {
  list: () => api.get('/subjects'),
  show: (id) => api.get(`/subjects/${id}`),
  store: (data) => api.post('/subjects', data),
  update: (id, data) => api.put(`/subjects/${id}`, data),
  destroy: (id) => api.delete(`/subjects/${id}`),
};
