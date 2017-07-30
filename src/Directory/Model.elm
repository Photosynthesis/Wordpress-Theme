module Model exposing (Model, initial, paginationConfig)

import Date exposing (Date)
import Commands exposing (getCommunities)
import Communities exposing (Community)
import Pagination exposing (Pagination)


type alias Model =
    { communities : Pagination Community
    , currentDate : Maybe Date
    }


initial : Int -> ( Model, Cmd (Pagination.Msg Community) )
initial page =
    let
        ( communitiesPagination, paginationCmd ) =
            Pagination.initial paginationConfig page
    in
        ( { communities = communitiesPagination
          , currentDate = Nothing
          }
        , paginationCmd
        )


paginationConfig : Pagination.Config Community
paginationConfig =
    Pagination.makeConfig getCommunities
