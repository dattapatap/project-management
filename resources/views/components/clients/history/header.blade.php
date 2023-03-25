<style>
    .tbl-projects td, .tbl-projects th {
        padding: .20rem;
        vertical-align: top;
        border: 1px solid #c9d2e0;
    }
</style>
<div class="comp_header_detail">

    <div class="comp_header_item company_header" >

        <h3 class="fs-17 company_name">
            <a @if($client->website_link) href="{{$client->website_link}}" @else  href="javascript:void(0)"  @endif target="_blank">
                {{ $client->name }}
            </a>
        </h3>
        <p class="company_address fs-12">
            {{ $client->address }}
        </p>
        <span class="comp_cont_person fs-13">
            @isset( $client->mobile )
            {{ $client->cont_person }}@isset($client->designation) ({{ $client->designation }}) @endisset : {{ $client->mobile }}
            @endisset
        </span>
    </div>

    <div class="comp_header_item company_header" style="justify-content: center;display: flex;">
        <ul class="customer-card-content pl-0">
            @if($client->status == "Matured" && $client->is_active)
                <li>
                    <p class="customer-since">
                        Customer Since :
                        <span> {{ Carbon\Carbon::parse($client->active_from)->diffForHumans() }}</span>
                    </p>
                </li>
            @endif

            <li>
                <p class="visiting-card">
                    Visiting Card :
                    @php
                            $docsImg = DB::table('client_docs')->where('client', $client->id)
                                            ->where('doc_type', 'Visiting Card')->get();
                    @endphp

                    @if(!$docsImg->isEmpty())
                        @foreach ($docsImg as $item)
                            <a  href="{{ asset('storage/'. $item->files.'')}}"
                            class="p-1 view-visiting-card gallery-popup" @if($loop->index != 0 )  style="display:none;" @endif>
                                <i class="mdi mdi-eye fs-17"></i>
                            </a>
                        @endforeach
                    @endif

                    <a class="p-1 add-visiting-card">
                        <i class="mdi mdi-plus-circle fs-17"></i>
                    </a>
                </p>
            </li>
        </ul>
    </div>

    @if(!$client->projects->isEmpty())
        <div class="comp_header_item company_header" style="justify-content: center;display: flex;" >
            <table class="table table-bordered tbl-projects">
                <tr>
                    <th> Projects </th>
                    <th class="text-center"> Status </th>
                </tr>
                @foreach($client->projects as $items)
                    <tr>
                        <td> {{ $items->project_name  }}</td>
                        <td class="text-center">
                            @if(  $items->status == "Not Assigned")
                                <span class="badge badge-danger">{{ $items->status  }} </span>
                            @elseif( $items->status == "Assigned")
                                <span class="badge badge-warning">{{ $items->status  }} </span>
                            @elseif( $items->status == "Working Progress")
                                <span class="badge badge-info">{{ $items->status  }} </span>
                            @elseif( $items->status == "Pending")
                                <span class="badge badge-warning">{{ $items->status  }} </span>
                            @else
                                <span class="badge badge-success">{{ $items->status  }} </span>
                            @endif
                        </td>
                    </tr>
                @endforeach

            </table>
        </div>
    @endif

</div>
