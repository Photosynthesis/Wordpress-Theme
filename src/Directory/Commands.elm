module Commands exposing (getCommunities)

import Http
import Json.Decode as Decode
import Communities exposing (Community)
import Decoders exposing (communityDecoder)
import Pagination
import Routing exposing (FilterParam(..))


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


getCommunities : List FilterParam -> Int -> Http.Request (Pagination.FetchResponse Community)
getCommunities filters page =
    Decode.map2 Pagination.FetchResponse
        (Decode.field "listings" (Decode.list communityDecoder))
        (Decode.field "totalCount" Decode.int)
        |> Http.get
            ("/wp-json/v1/directory/entries/"
                ++ "?page="
                ++ toString page
                ++ if not (List.isEmpty filters) then
                    "&" ++ String.join "&" (List.map filterToApiQuery filters)
                   else
                    ""
            )
