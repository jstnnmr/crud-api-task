import api from './client';

export const teamApi = {
  getInvitations: () => api.get('/team/invitations'),
  invite: (data) => api.post('/team/invite', data),
  acceptInvitation: (token) => api.post(`/team/invitations/${token}/accept`),
  declineInvitation: (token) => api.post(`/team/invitations/${token}/decline`),
  getCollaborators: (taskId) => api.get(`/team/tasks/${taskId}/collaborators`),
  removeCollaborator: (taskId, collabId) =>
    api.delete(`/team/tasks/${taskId}/collaborators/${collabId}`),
  getActivities: (taskId) => api.get(`/team/tasks/${taskId}/activities`),
};
