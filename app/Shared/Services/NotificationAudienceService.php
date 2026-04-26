<?php

namespace App\Shared\Services;

use App\Domains\Document\Models\Document;
use App\Domains\Document\Models\DocumentApprovalStep;
use App\Domains\User\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationAudienceService
{
    public static function isVisibleTo(User $user, DatabaseNotification $notification): bool
    {
        $data = $notification->data;
        $type = $data['type'] ?? null;

        return match ($type) {
            'document_approval_requested' => self::canSeeDocumentApprovalRequest($user, $data),
            'document_status_changed' => self::canSeeDocumentStatusChange($user, $data),
            default => true,
        };
    }

    private static function canSeeDocumentApprovalRequest(User $user, array $data): bool
    {
        if (! empty($data['target_user_id']) && (int) $data['target_user_id'] !== (int) $user->id) {
            return false;
        }

        if (! empty($data['approval_step_id'])) {
            $step = DocumentApprovalStep::query()->find($data['approval_step_id']);

            if ($step && (int) $step->approver_id !== (int) $user->id) {
                return false;
            }
        }

        if (! empty($data['target_role']) && ! $user->hasRole($data['target_role'])) {
            return (bool) $user->hasPermission('documents.approve');
        }

        return $user->hasAnyPermission(['documents.approve', 'documents.review']);
    }

    private static function canSeeDocumentStatusChange(User $user, array $data): bool
    {
        if (! empty($data['target_user_id'])) {
            return (int) $data['target_user_id'] === (int) $user->id;
        }

        if (empty($data['document_id'])) {
            return true;
        }

        $document = Document::query()->find($data['document_id']);

        return $document && (int) $document->created_by === (int) $user->id;
    }
}
