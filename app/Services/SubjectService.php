<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\SubjectRepository;
use App\Support\ServiceReturn;

class SubjectService
{
    public function __construct(
        protected SubjectRepository $subjectRepository
    ) {}

    public function getAll(int $userId): ServiceReturn
    {
        $subjects = $this->subjectRepository->getAllByUser(userId: $userId);
        return ServiceReturn::success(data: $subjects);
    }

    public function getByIdWithTasks(int $id, int $userId): ServiceReturn
    {
        $subject = $this->subjectRepository->findByIdAndUserWithTasks(id: $id, userId: $userId);
        if (!$subject) {
            return ServiceReturn::error(message: 'Subject not found', status: 404);
        }
        return ServiceReturn::success(data: $subject);
    }

    public function create(int $userId, array $data): ServiceReturn
    {
        $subject = $this->subjectRepository->create(data: [
            'user_id' => $userId,
            'name'    => $data['name'],
            'color'   => $data['color'] ?? '#8e7dff',
        ]);
        return ServiceReturn::success(data: $subject, status: 201);
    }

    public function update(int $id, int $userId, array $data): ServiceReturn
    {
        $subject = $this->subjectRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$subject) {
            return ServiceReturn::error(message: 'Subject not found', status: 404);
        }
        $subject = $this->subjectRepository->update(subject: $subject, data: $data);
        return ServiceReturn::success(data: $subject);
    }

    public function delete(int $id, int $userId): ServiceReturn
    {
        $subject = $this->subjectRepository->findByIdAndUser(id: $id, userId: $userId);
        if (!$subject) {
            return ServiceReturn::error(message: 'Subject not found', status: 404);
        }
        $this->subjectRepository->delete(subject: $subject);
        return ServiceReturn::success(message: 'Subject deleted');
    }
}