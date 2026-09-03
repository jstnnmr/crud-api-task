import api from './client';

export const categoriesApi = {
  list: () => api.get('/categories'),
  store: (data) => api.post('/categories', data),
  update: (id, data) => api.put(`/categories/${id}`, data),
  destroy: (id) => api.delete(`/categories/${id}`),
};
