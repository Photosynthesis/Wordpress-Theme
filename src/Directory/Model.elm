module Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Commands exposing (getCommunities, CommunitiesRequestData)
import Communities exposing (Community)
import Pagination exposing (Pagination)
import Routing exposing (Route(..), FilterParam(..))


type alias Model =
    { communities : Pagination Community CommunitiesRequestData
    , currentDate : Maybe Date
    , route : Route
    }


initial : Route -> ( Model, Cmd (Pagination.Msg Community) )
initial route =
    let
        ( page, filters ) =
            Routing.getPageAndFilters route

        requestData =
            CommunitiesRequestData filters (Routing.getOrdering route)

        ( communitiesPagination, paginationCmd ) =
            Pagination.initial paginationConfig requestData page
    in
        ( { communities = communitiesPagination
          , currentDate = Nothing
          , route = route
          }
        , paginationCmd
        )


paginationConfig : Pagination.Config Community CommunitiesRequestData
paginationConfig =
    Pagination.makeConfig getCommunities
