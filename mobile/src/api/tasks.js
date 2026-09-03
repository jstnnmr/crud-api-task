import api from './client';

export const tasksApi = {
  list: (params) => api.get('/tasks', { params }),
  show: (id) => api.get(`/tasks/${id}`),
  store: (data) => api.post('/tasks', data),
  update: (id, data) => api.put(`/tasks/${id}`, data),
  destroy: (id) => api.delete(`/tasks/${id}`),
  complete: (id) => api.patch(`/tasks/${id}/complete`),
  myTasks: () => api.get('/my-tasks'),
};
