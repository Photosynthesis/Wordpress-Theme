module Commands exposing (getCommunities)

import Http
import Json.Decode as Decode
import Communities exposing (Community)
import Decoders exposing (communityDecoder)
import Pagination


getCommunities : Int -> Http.Request (Pagination.FetchResponse Community)
getCommunities page =
    Decode.map2 Pagination.FetchResponse
        (Decode.field "listings" (Decode.list communityDecoder))
        (Decode.field "totalCount" Decode.int)
        |> Http.get ("/wp-json/v1/directory/entries/" ++ "?page=" ++ toString page)
