<style>
    /* Premium Client Profile Header Stylesheet Overrides */
    .comp_header_detail {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border-radius: 16px;
        padding: 24px 30px;
        color: #ffffff !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 25px;
        flex-wrap: wrap;
        font-family: 'Outfit', 'Inter', sans-serif;
    }

    .comp_header_detail .company_header {
        width: 31%;
        min-width: 250px;
    }

    .comp_header_detail a {
        color: #ffffff !important;
        text-decoration: none !important;
        transition: opacity 0.2s ease;
    }

    .comp_header_detail a:hover {
        opacity: 0.85;
    }

    .company_name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 20px;
        margin-top: 0;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
        text-transform: capitalize;
    }

    .company_address {
        font-size: 12.5px;
        opacity: 0.85;
        margin-bottom: 12px;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
        gap: 6px;
    }

    .company_address i {
        margin-top: 2px;
        font-size: 14px;
        color: #ff4d4f;
    }

    .comp_cont_person {
        font-size: 13px;
        background: rgba(255, 255, 255, 0.15);
        padding: 6px 12px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }

    .comp_cont_person i {
        font-size: 14px;
    }

    /* Customer Since & Visiting Card badge group */
    .customer-card-content {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }

    .customer-since {
        background: rgba(255, 193, 7, 0.18) !important;
        border: 1px solid rgba(255, 193, 7, 0.4) !important;
        color: #ffc107 !important;
        border-radius: 8px;
        padding: 6px 14px !important;
        font-size: 12.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 0 !important;
    }

    .visiting-card {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        border-radius: 8px;
        padding: 6px 14px !important;
        font-size: 12.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 0 !important;
    }

    .visiting-card-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 4px;
    }

    .visiting-card-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transition: all 0.2s ease;
        color: #ffffff !important;
    }

    .visiting-card-actions a:hover {
        background: #ffffff;
        color: #1e3c72 !important;
    }

    /* Projects sub-table */
    .tbl-projects {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 12px;
        overflow: hidden;
        border-collapse: separate !important;
        border-spacing: 0;
        width: 100%;
        margin-bottom: 0;
    }

    .tbl-projects th {
        background: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none !important;
        padding: 10px 14px !important;
        font-weight: 700;
    }

    .tbl-projects td {
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: none !important;
        color: rgba(255, 255, 255, 0.9) !important;
        font-size: 12.5px;
        padding: 10px 14px !important;
    }

    /* Back Nav button transformation to matching circle */
    .btn-back {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50% !important;
        background: #ffffff !important;
        border: 1px solid rgba(220, 220, 235, 0.8) !important;
        color: #495057 !important;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }

    .btn-back:hover {
        background: #7F00FF !important;
        color: #ffffff !important;
        border-color: #7F00FF !important;
        transform: translateX(-3px);
        box-shadow: 0 4px 12px rgba(127, 0, 255, 0.25);
    }

    /* Beautiful Pill-Style Glass Toggle Bar matching client list */
    .nav-dept {
        border-bottom: none !important;
        background: #f1f2f7 !important;
        padding: 6px !important;
        border-radius: 14px !important;
        display: inline-flex !important;
        gap: 6px !important;
        width: auto !important;
        flex-wrap: wrap;
        margin-top: 15px;
        margin-bottom: 20px;
        margin-left: 15px;
    }

    .nav-dept .nav-item {
        margin-bottom: 0 !important;
    }

    .nav-dept .nav-link {
        border: none !important;
        border-radius: 10px !important;
        padding: 10px 22px !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        color: #74788d !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background: transparent !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 8px !important;
        font-size: 13.5px !important;
        box-shadow: none !important;
    }

    .nav-dept .nav-link.active {
        background: #ffffff !important;
        color: #7F00FF !important;
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.05) !important;
    }

    .nav-dept .nav-link:hover:not(.active) {
        color: #343a40 !important;
        background: rgba(255, 255, 255, 0.5) !important;
    }

    /* Tab Content - Padding and Spacing Optimization */
    .tab-content {
        padding: 24px 30px !important;
        margin-left: 15px !important;
        margin-right: 15px !important;
    }

    /* Sub-pane headers & details custom flat corporate styling */
    .lbl-heading-pane {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #1e3c72;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none !important;
    }

    .tab-content .table {
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border-collapse: separate !important;
        border: 1px solid #edf2f9 !important;
    }

    .tab-content .table td {
        padding: 14px 18px !important;
        vertical-align: middle !important;
        border-top: none !important;
        border-bottom: 1px solid #edf2f9 !important;
        font-size: 13.5px !important;
    }

    .tab-content .table tr:last-child td {
        border-bottom: none !important;
    }

    /* Standard card border override to modern shadow */
    .card-top-border {
        border-top: 3px solid #7F00FF !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02) !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: none !important;
    }

    /* Form Containers - Beautiful Border Frame & Background */
    .tab-pane form {
        border: 1px solid rgba(220, 220, 235, 0.8) !important;
        border-radius: 16px !important;
        padding: 30px 35px !important;
        background-color: #fafbfc !important;
        margin-top: 10px !important;
        margin-bottom: 25px !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.015) !important;
    }

    /* Form Elements and Fields Inside the Tabs */
    .tab-pane label {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #343a40 !important;
        margin-bottom: 6px !important;
        text-transform: capitalize;
    }

    .tab-pane .form-control,
    .tab-pane select,
    .tab-pane textarea {
        border-radius: 10px !important;
        border: 1px solid rgba(210, 210, 225, 0.7) !important;
        padding: 10px 14px !important;
        font-family: 'Outfit', sans-serif !important;
        font-size: 13.5px !important;
        color: #333333 !important;
        transition: all 0.25s ease !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
    }

    .tab-pane .form-control:focus,
    .tab-pane select:focus,
    .tab-pane textarea:focus {
        border-color: #7F00FF !important;
        box-shadow: 0 0 0 3px rgba(127, 0, 255, 0.15) !important;
        outline: none !important;
    }

    .tab-pane textarea {
        padding: 12px 14px !important;
    }

    /* Premium styled CTA buttons */
    .tab-pane .creatBtn,
    .tab-pane .btn-primary {
        border-radius: 10px !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 700 !important;
        letter-spacing: 0.5px !important;
        padding: 10px 24px !important;
        background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%) !important;
        border: none !important;
        color: #ffffff !important;
        transition: all 0.25s ease !important;
        box-shadow: 0 4px 15px rgba(127,0,255,0.2) !important;
        text-transform: uppercase !important;
    }

    .tab-pane .creatBtn:hover,
    .tab-pane .btn-primary:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 8px 20px rgba(127,0,255,0.3) !important;
    }

    /* File upload customizations */
    .tab-pane .custom-file-label {
        border-radius: 10px !important;
        border: 1px solid rgba(210, 210, 225, 0.7) !important;
        padding: 10px 14px !important;
        height: auto !important;
        font-family: 'Outfit', sans-serif !important;
        font-size: 13px !important;
        color: #74788d !important;
        background-color: #ffffff !important;
        line-height: 1.5 !important;
    }

    .tab-pane .custom-file-label::after {
        border-top-right-radius: 9px !important;
        border-bottom-right-radius: 9px !important;
        padding: 10px 14px !important;
        height: auto !important;
        line-height: 1.5 !important;
        background-color: #f1f2f7 !important;
        color: #495057 !important;
        font-weight: 600 !important;
    }

    /* Inline content layouts (like attach container) */
    .form-inline-content {
        background: #f8f9fc !important;
        border: 1px solid rgba(215, 215, 230, 0.7) !important;
        border-radius: 12px !important;
        padding: 12px 18px !important;
        margin-bottom: 15px !important;
        display: flex !important;
        align-items: center !important;
        gap: 15px !important;
    }

    /* History & Audit Timeline Styling */
    .history-content {
        background: #f8f9fc !important;
        border: 1px solid rgba(215, 215, 230, 0.7) !important;
        border-radius: 16px !important;
        padding: 24px !important;
    }

    .history-lst {
        list-style: none !important;
        padding-left: 0 !important;
        margin-bottom: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 16px !important;
    }

    .history-lst li {
        background: #ffffff !important;
        border-left: 4px solid #7F00FF !important;
        border-radius: 10px !important;
        padding: 16px 20px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02) !important;
        display: grid !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        text-align: left !important;
    }

    .history-lst li:hover {
        transform: translateX(3px) !important;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.04) !important;
    }

    /* Meta text */
    .history-lst li > span:first-child {
        font-family: 'Outfit', sans-serif !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #888da8 !important;
        letter-spacing: 0.5px !important;
    }

    /* Remarks text */
    .history-lst li > span:nth-child(2) {
        font-size: 13.5px !important;
        color: #333333 !important;
        font-weight: 500 !important;
        padding-left: 0 !important;
    }

    /* Status text */
    .history-lst li > span:nth-child(3) {
        font-size: 12.5px !important;
        color: #7F00FF !important;
        font-weight: 600 !important;
        padding-left: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
    }

    /* Added Time / Schedule details */
    .history-lst li > span:nth-child(4) {
        font-size: 12.5px !important;
        color: #74788d !important;
        padding-left: 0 !important;
    }

    /* Documents Section & Gallery Styling */
    .section_gallery {
        background: #f8f9fc !important;
        border: 1px solid rgba(215, 215, 230, 0.7) !important;
        border-radius: 16px !important;
        padding: 24px !important;
    }

    .content_docs {
        background: #ffffff !important;
        border: 1px solid rgba(230, 230, 245, 0.8) !important;
        border-radius: 14px !important;
        padding: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important;
        transition: all 0.25s ease !important;
        text-align: center !important;
        margin-bottom: 20px !important;
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: space-between !important;
        height: 160px !important; /* Extremely neat compact card height */
    }

    .content_docs:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 10px 22px rgba(0,0,0,0.06) !important;
        border-color: #7F00FF !important;
    }

    .content_docs img {
        height: 75px !important; /* Perfect compact image height */
        width: auto !important;
        max-width: 70px !important;
        object-fit: contain !important;
        border-radius: 8px !important;
        margin-bottom: 8px !important;
        transition: transform 0.2s ease !important;
    }

    .content_docs:hover img {
        transform: scale(1.05) !important;
    }

    /* Action button container on doc cards */
    .content_docs .img-btn {
        position: absolute !important;
        top: 10px !important;
        right: 10px !important;
        bottom: auto !important;
        left: auto !important;
        opacity: 0 !important;
        visibility: hidden !important;
        transition: all 0.25s ease !important;
    }

    .content_docs:hover .img-btn {
        opacity: 1 !important;
        visibility: visible !important;
    }

    .content_docs .img-btn a {
        background: #7F00FF !important;
        color: #ffffff !important;
        width: 32px !important;
        height: 32px !important;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 10px rgba(127, 0, 255, 0.3) !important;
        transition: all 0.2s ease !important;
    }

    .content_docs .img-btn a:hover {
        transform: scale(1.1) !important;
        background: #E100FF !important;
    }

    .content_docs .docs-text {
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        color: #343a40 !important;
        text-align: center !important;
        width: 100% !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    .add-doc-btn {
        background: rgba(127, 0, 255, 0.1) !important;
        color: #7F00FF !important;
        border: 1px solid rgba(127, 0, 255, 0.2) !important;
        padding: 8px 16px !important;
        border-radius: 10px !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
    }

    .add-doc-btn:hover {
        background: #7F00FF !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(127, 0, 255, 0.2) !important;
    }
</style>

<div class="comp_header_detail">

    <!-- Column 1: Company Profile Info -->
    <div class="comp_header_item company_header">
        <h3 class="company_name">
            <a @if($client->website_link) href="{{$client->website_link}}" @else href="javascript:void(0)" @endif target="_blank">
                <i class="mdi mdi-office-building-outline mr-1"></i> {{ $client->name }}
            </a>
        </h3>
        <p class="company_address">
            <i class="mdi mdi-map-marker-outline"></i> {{ $client->address }}
        </p>
        <span class="comp_cont_person">
            @isset($client->mobile)
                <i class="mdi mdi-account-box-outline"></i> {{ $client->cont_person }}@isset($client->designation) ({{ $client->designation }}) @endisset : {{ $client->mobile }}
            @endisset
        </span>
    </div>

    <!-- Column 2: Badges and Visiting Card Action Links -->
    <div class="comp_header_item company_header d-flex justify-content-center align-items-center">
        <ul class="customer-card-content pl-0">
            @if($client->status == "Matured" && $client->is_active)
                <li>
                    <p class="customer-since">
                        <i class="mdi mdi-certificate-outline"></i> Customer Since:
                        <span>{{ Carbon\Carbon::parse($client->active_from)->diffForHumans() }}</span>
                    </p>
                </li>
            @endif

            <li>
                <div class="visiting-card">
                    <i class="mdi mdi-card-account-phone-outline"></i> Visiting Card:
                    @php
                        $docsImg = DB::table('client_docs')->where('client', $client->id)
                                        ->where('doc_type', 'Visiting Card')->get();
                    @endphp

                    <div class="visiting-card-actions">
                        @if(!$docsImg->isEmpty())
                            @foreach ($docsImg as $item)
                                <a href="{{ asset('storage/'. $item->files.'')}}" class="view-visiting-card gallery-popup" @if($loop->index != 0) style="display:none;" @endif data-toggle="tooltip" title="View Card">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            @endforeach
                        @endif

                        <a class="add-visiting-card" data-toggle="tooltip" title="Add Card">
                            <i class="mdi mdi-plus-circle-outline"></i>
                        </a>
                    </div>
                </div>
            </li>
        </ul>
    </div>

    <!-- Column 3: Mini Projects Status Table -->
    @if(!$client->projects->isEmpty())
        <div class="comp_header_item company_header d-flex justify-content-end align-items-center">
            <table class="table tbl-projects">
                <thead>
                    <tr>
                        <th><i class="mdi mdi-folder-outline mr-1"></i> Projects</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($client->projects as $items)
                        <tr>
                            <td>{{ $items->project_name }}</td>
                            <td class="text-center">
                                @if($items->status == "Not Assigned")
                                    <span class="badge badge-soft-danger px-2 py-1" style="border-radius: 6px;">{{ $items->status }}</span>
                                @elseif($items->status == "Assigned")
                                    <span class="badge badge-soft-warning px-2 py-1" style="border-radius: 6px;">{{ $items->status }}</span>
                                @elseif($items->status == "Working Progress")
                                    <span class="badge badge-soft-info px-2 py-1" style="border-radius: 6px;">{{ $items->status }}</span>
                                @elseif($items->status == "Pending")
                                    <span class="badge badge-soft-warning px-2 py-1" style="border-radius: 6px;">{{ $items->status }}</span>
                                @else
                                    <span class="badge badge-soft-success px-2 py-1" style="border-radius: 6px;">{{ $items->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
