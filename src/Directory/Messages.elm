module Messages exposing (..)

import Date exposing (Date)
import Communities exposing (Community)
import Pagination


type Msg
    = SetCurrentDate Date
    | PreviousPage
    | NextPage
    | JumpToPage Int
    | CommunityPagination (Pagination.Msg Community)
