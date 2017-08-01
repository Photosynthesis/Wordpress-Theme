module Commands exposing (getCommunities, CommunitiesRequestData)

import Http
import Json.Decode as Decode
import Communities exposing (Community)
import Decoders exposing (communityDecoder)
import Pagination
import Routing exposing (FilterParam(..), Ordering(..))


filterToApiQuery : FilterParam -> String
filterToApiQuery filter =
    case filter of
        VisitorsFilter ->
            "visitors[]=Yes"

        MembersFilter ->
            "members[]=Yes"

        EstablishedFilter ->
            "status[]=Established"

        FormingFilter ->
            "status[]=Forming,Re-forming"

        FICMemberFilter ->
            "membership[]=Yes"

        CommunesFilter ->
            "type[]=Commune"

        EcovillagesFilter ->
            "type[]=Ecovillage"

        CohousingFilter ->
            "type[]=Cohousing"

        CoopFilter ->
            "description[]=coop"

        ReligiousFilter ->
            "type[]=Spiritual"

        JewishFilter ->
            "spiritual[]=Jewish"

        ChristianFilter ->
            "spiritual[]=Christian"


orderingToApiQuery : Maybe Ordering -> String
orderingToApiQuery =
    let
        orderingToQuery ordering =
            case ordering of
                UpdatedDate ->
                    "order=updated"

                CreatedDate ->
                    "order=created"
    in
        Maybe.map orderingToQuery >> Maybe.withDefault ""


type alias CommunitiesRequestData =
    { filters : List FilterParam
    , ordering : Maybe Ordering
    }


getCommunities : CommunitiesRequestData -> Int -> Http.Request (Pagination.FetchResponse Community)
getCommunities { filters, ordering } page =
    let
        filterQueryString =
            if not (List.isEmpty filters) then
                "&" ++ String.join "&" (List.map filterToApiQuery filters)
            else
                ""

        orderQueryString =
            if not (String.isEmpty <| orderingToApiQuery ordering) then
                "&" ++ orderingToApiQuery ordering
            else
                ""
    in
        Decode.map2 Pagination.FetchResponse
            (Decode.field "listings" (Decode.list communityDecoder))
            (Decode.field "totalCount" Decode.int)
            |> Http.get
                (String.join ""
                    [ "/wp-json/v1/directory/entries/"
                    , "?page="
                    , toString page
                    , filterQueryString
                    , orderQueryString
                    ]
                )
