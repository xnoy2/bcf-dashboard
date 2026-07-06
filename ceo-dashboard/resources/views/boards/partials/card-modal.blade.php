{{-- Card detail modal. All content is populated by boards.js from GET /cards/{id}. --}}
<div class="modal fade" id="cardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header align-items-start">
                <div class="flex-grow-1 me-2">
                    <h5 class="modal-title" id="cmTitle" contenteditable="true"
                        spellcheck="false" style="border-radius:6px;padding:.1rem .3rem;"></h5>
                    <div class="small text-muted">in list <span id="cmListName" class="fw-semibold"></span></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Action bar --}}
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-tag"></i> Labels
                        </button>
                        <div class="dropdown-menu p-2" style="min-width:230px;" id="cmLabelMenu"></div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-clock"></i> Dates
                        </button>
                        <div class="dropdown-menu p-3" style="min-width:260px;" id="cmDateMenu">
                            <label class="form-label small">Due date</label>
                            <input type="datetime-local" id="cmDueInput" class="form-control form-control-sm mb-2">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="cmCompleted">
                                <label class="form-check-label small" for="cmCompleted">Mark complete</label>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary" id="cmSaveDue">Save</button>
                                <button class="btn btn-sm btn-outline-secondary" id="cmClearDue">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <i class="bi bi-people"></i> Members
                        </button>
                        <div class="dropdown-menu p-2" style="min-width:230px;" id="cmMemberMenu"></div>
                    </div>
                    <button class="btn btn-sm btn-light" id="cmAttachBtn"><i class="bi bi-paperclip"></i> Attachment</button>
                    <input type="file" id="cmFileInput" multiple hidden
                           accept=".jpg,.jpeg,.png,.gif,.webp,.heic,.heif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv">
                </div>

                <div class="cm-grid">
                    {{-- Left column --}}
                    <div>
                        <div id="cmLabelsRow" class="card-labels"></div>
                        <div id="cmDueRow" class="mb-2"></div>

                        <div class="cm-section-title"><i class="bi bi-text-left"></i> Description</div>
                        <textarea id="cmDescription" class="form-control" rows="3"
                                  placeholder="Add a more detailed description…"></textarea>
                        <div class="mt-1">
                            <button id="cmSaveDesc" class="btn btn-sm btn-primary d-none">Save</button>
                            <button id="cmCancelDesc" class="btn btn-sm btn-light d-none">Cancel</button>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <div class="cm-section-title"><i class="bi bi-check2-square"></i> Checklist</div>
                            <button id="cmAddChecklist" class="btn btn-sm btn-link text-decoration-none">+ Add checklist</button>
                        </div>
                        <div id="cmChecklists"></div>

                        <div class="cm-section-title"><i class="bi bi-paperclip"></i> Attachments</div>
                        <div id="cmAttachments" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    {{-- Right column: comments & activity --}}
                    <div>
                        <div class="cm-section-title"><i class="bi bi-chat-dots"></i> Comments and activity</div>
                        <textarea id="cmCommentBody" class="form-control mb-2" rows="2"
                                  placeholder="Write a comment… use @ to mention"></textarea>
                        <button id="cmPostComment" class="btn btn-sm btn-primary mb-2">Comment</button>
                        <div id="cmFeed"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button id="cmDelete" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete card</button>
            </div>
        </div>
    </div>
</div>
