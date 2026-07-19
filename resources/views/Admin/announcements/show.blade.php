@extends('layouts.admin')
@section('title','Announcement Details')
@section('content')

    <div style="max-width:750px;margin:0 auto">

        {{-- Back button --}}
        <a href="{{ route('admin.announcements') }}"
           style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">
            ← Back to Announcements
        </a>

        <div class="panel">

            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1.5px solid #f0e8e8">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                    <span class="badge {{
                        $announcement->status === 'live' ? 'badge-green' : 'badge-gray'
                    }}" style="font-size:.72rem">
                        {{ strtoupper($announcement->status) }}
                    </span>
                        <span class="badge badge-blue" style="font-size:.72rem">
                        {{ $announcement->audience }}
                    </span>
                    </div>
                    <h1 style="font-size:1.3rem;font-weight:800;color:#1a1a1a;line-height:1.3">
                        {{ $announcement->title }}
                    </h1>
                </div>
            </div>

            {{-- Meta info --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px">
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Author</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $announcement->author }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Published</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">
                        {{ $announcement->published_at ? $announcement->published_at->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Total Views</div>
                    <div style="font-size:.85rem;font-weight:700;color:#8b1c2c">{{ number_format($announcement->views) }}</div>
                </div>
            </div>

            {{-- Content --}}
            <div style="margin-bottom:24px">
                <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Content</div>
                <div style="background:#fafafa;border:1.5px solid #f0e8e8;border-radius:8px;padding:18px 20px;font-size:.88rem;color:#333;line-height:1.7;white-space:pre-wrap">{{ $announcement->content }}</div>
            </div>

            {{-- Attachment --}}
            @if($announcement->attachment)
                @php
                    $attachmentUrl = asset('storage/' . $announcement->attachment);
                    $attachmentExtension = strtolower(pathinfo($announcement->attachment, PATHINFO_EXTENSION));
                    $isImageAttachment = in_array($attachmentExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                @endphp
                <div style="margin-bottom:24px">
                    <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Attachment</div>
                    @if($isImageAttachment)
                        <div style="background:#fff;border:1.5px solid #f0e8e8;border-radius:10px;padding:10px">
                            <img src="{{ $attachmentUrl }}"
                                 alt="Announcement image"
                                 style="width:100%;max-height:420px;object-fit:contain;border-radius:8px;background:#f8f1f1">
                        </div>
                        <a href="{{ $attachmentUrl }}"
                           target="_blank"
                           style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;color:#38a169;font-size:.82rem;font-weight:700;text-decoration:none">
                            <x-icon name="image" /> Open full image
                        </a>
                    @else
                        <a href="{{ $attachmentUrl }}"
                           target="_blank"
                           style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;background:#f0faf4;border:1.5px solid #38a169;border-radius:8px;color:#38a169;font-size:.82rem;font-weight:600;text-decoration:none">
                            <x-icon name="paperclip" /> View Attachment
                        </a>
                    @endif
                </div>
            @endif

            {{-- Footer actions --}}
            <div style="display:flex;gap:10px;padding-top:16px;border-top:1.5px solid #f0e8e8">
                <a href="{{ route('admin.announcements') }}" class="btn btn-outline">← Back</a>

                {{-- Delete form --}}
                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}"
                      data-confirm-message="Delete this announcement? This will remove it from both web and mobile."
                      data-confirm-action="Delete"
                      style="margin-left:auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="padding:7px 16px;background:#e53e3e;color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer">
                        <x-icon name="trash" /> Delete
                    </button>
                </form>
            </div>

        </div>
    </div>

@endsection
