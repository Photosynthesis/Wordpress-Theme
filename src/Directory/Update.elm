module Update exposing (update)

import Navigation
import Messages exposing (Msg(..))
import Model exposing (Model, paginationConfig)
import Pagination exposing (Pagination)
import Ports
import Routing exposing (Route(..), FilterParam(..), reverse)


updateUrl : Route -> Model -> ( Model, Cmd Msg )
updateUrl route model =
    let
        withUpdatedRoute =
            { model | route = route }

        jumpToPage page =
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.jumpTo paginationConfig model.communities page
            in
                ( { withUpdatedRoute | communities = updatedPagination }
                , Cmd.map CommunityPagination paginationCmd
                )

        communityFilters =
            Pagination.getData model.communities
                |> (\{ filters } -> filters)

        communityOrdering =
            Pagination.getData model.communities
                |> (\{ ordering } -> ordering)

        ( page, filters ) =
            Routing.getPageAndFilters route

        ordering =
            Routing.getOrdering route

        updatedRequestData =
            Pagination.getData model.communities
                |> (\data -> { data | filters = filters, ordering = ordering })
    in
        if filters /= communityFilters || ordering /= communityOrdering then
            Pagination.updateData paginationConfig model.communities updatedRequestData
                |> (\( m, c ) ->
                        ( { withUpdatedRoute | communities = m }, Cmd.map CommunityPagination c )
                   )
        else if page /= Pagination.getPage model.communities then
            jumpToPage page
        else
            ( model, Cmd.none )


update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        SetCurrentDate currentDate ->
            ( { model | currentDate = Just currentDate }, Cmd.none )

        UrlChange newRoute ->
            updateUrl newRoute model

        NavigateTo newRoute ->
            ( model
            , Cmd.batch
                [ Navigation.newUrl <| reverse newRoute
                , Ports.scrollTo "main"
                , Ports.setPageTitle <| Routing.getPageTitle newRoute
                ]
            )

        CommunityPagination subMsg ->
            let
                ( paginationModel, paginationCmd ) =
                    Pagination.update subMsg model.communities
            in
                ( { model | communities = paginationModel }
                , Cmd.map CommunityPagination paginationCmd
                )
