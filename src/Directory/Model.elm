module Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Commands exposing (getCommunities)
import Communities exposing (Community)
import Pagination exposing (Pagination)
import Routing exposing (Route(..), FilterParam(..))


type alias Model =
    { communities : Pagination Community FilterParam
    , currentDate : Maybe Date
    , route : Route
    }


initial : Int -> List FilterParam -> ( Model, Cmd (Pagination.Msg Community) )
initial page filters =
    let
        ( communitiesPagination, paginationCmd ) =
            Pagination.initial paginationConfig filters page
    in
        ( { communities = communitiesPagination
          , currentDate = Nothing
          , route = Listings page filters
          }
        , paginationCmd
        )


paginationConfig : Pagination.Config Community FilterParam
paginationConfig =
    Pagination.makeConfig getCommunities
