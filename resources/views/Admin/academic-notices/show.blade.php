@extends('layouts.admin')
@section('title','Academic Notice Details')
@section('content')

    <div style="max-width:750px;margin:0 auto">

        {{-- Back --}}
        <a href="{{ route('admin.academic-notices') }}"
           style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">
            ← Back to Academic Notices
        </a>

        <div class="panel">

            {{-- Header --}}
            <div style="padding-bottom:16px;border-bottom:1.5px solid #f0e8e8;margin-bottom:20px">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px">
                    {{-- Type badge --}}
                    <span style="padding:4px 12px;border-radius:20px;font-size:.7rem;font-weight:700;background:{{
                    $academicNotice->type === 'academic' ? '#fee2e2' :
                    ($academicNotice->type === 'office'  ? '#e0f2fe' : '#fef9c3')
                }};color:{{
                    $academicNotice->type === 'academic' ? '#991b1b' :
                    ($academicNotice->type === 'office'  ? '#0369a1' : '#854d0e')
                }}">
                    {{ strtoupper($academicNotice->type) }}
                </span>

                    {{-- Status badge --}}
                    <span class="badge {{
                    $academicNotice->status === 'published' ? 'badge-green'  :
                    ($academicNotice->status === 'pending'  ? 'badge-yellow' : 'badge-gray')
                }}">{{ strtoupper($academicNotice->status) }}</span>
                </div>

                <h1 style="font-size:1.3rem;font-weight:800;color:#1a1a1a;line-height:1.4">
                    {{ $academicNotice->title }}
                </h1>
            </div>

            {{-- Meta grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px">
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Posted By</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $academicNotice->posted_by }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Department</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $academicNotice->department }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Audience</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $academicNotice->audience ?? 'All Students' }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Published</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">
                        {{ $academicNotice->published_at ? $academicNotice->published_at->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Scheduled</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">
                        {{ $academicNotice->scheduled_at ? $academicNotice->scheduled_at->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Expires</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">
                        {{ $academicNotice->expires_at ? $academicNotice->expires_at->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
            </div>

            {{-- Tags --}}
            @if($academicNotice->tags && count($academicNotice->tags) > 0)
                <div style="margin-bottom:20px">
                    <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">Tags</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        @foreach($academicNotice->tags as $tag)
                            <span style="background:#fdf0f1;color:#8b1c2c;border:1.5px solid #d9b8bc;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:600">
                    {{ $tag }}
                </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Content --}}
            <div style="margin-bottom:24px">
                <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Content</div>
                <div style="background:#fafafa;border:1.5px solid #f0e8e8;border-radius:8px;padding:18px 20px;font-size:.88rem;color:#333;line-height:1.8;white-space:pre-wrap;min-height:100px">
                    {{ $academicNotice->content ?? 'No content provided.' }}
                </div>
            </div>

            {{-- Attachment --}}
            @if($academicNotice->attachment)
                <div style="margin-bottom:24px">
                    <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Attachment</div>
                    <a href="{{ asset('storage/' . $academicNotice->attachment) }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;background:#f0faf4;border:1.5px solid #38a169;border-radius:8px;color:#38a169;font-size:.82rem;font-weight:600;text-decoration:none">
                        <x-icon name="paperclip" /> View Attachment
                    </a>
                </div>
            @endif

            {{-- Approve button (if pending) --}}
            @if($academicNotice->status === 'pending')
                <div style="background:#fffbea;border:1.5px solid #f6e05e;border-radius:8px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px">
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:#744210">⏳ Pending Approval</div>
                        <div style="font-size:.75rem;color:#92400e;margin-top:2px">This notice is waiting for admin approval before it gets published.</div>
                    </div>
                    <form method="POST" action="{{ route('admin.academic-notices.approve', $academicNotice) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                style="padding:8px 18px;background:#38a169;color:#fff;border:none;border-radius:8px;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap">
                            <x-icon name="check-circle" /> Approve & Publish
                        </button>
                    </form>
                </div>
            @endif

            {{-- Footer actions --}}
            <div style="display:flex;gap:10px;padding-top:16px;border-top:1.5px solid #f0e8e8;align-items:center">
                <a href="{{ route('admin.academic-notices') }}" class="btn btn-outline">← Back</a>

                <form method="POST" action="{{ route('admin.academic-notices.destroy', $academicNotice) }}"
                      data-confirm-message="Delete this academic notice?"
                      data-confirm-action="Delete"
                      style="margin-left:auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="padding:7px 16px;background:#e53e3e;color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer">
                        <x-icon name="trash" /> Delete Notice
                    </button>
                </form>
            </div>

        </div>
    </div>

@endsection
