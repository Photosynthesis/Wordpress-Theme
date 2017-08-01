module Update exposing (update)

{-| Contains Functions For Modifying the Application State.
-}

import Commands
import Messages exposing (Msg(..))
import Model exposing (Model, paginationConfig)
import Pagination exposing (Pagination)
import Routing exposing (Route(..), FilterParam(..), reverse)


{-| Make Model Changes & Queue Commands Related to Page Changes.
-}
updateUrl : Route -> Model -> ( Model, Cmd Msg )
updateUrl route model =
    let
        updatedModel =
            { model | route = route, searchString = updatedSearchString }

        jumpToPage page =
            let
                ( updatedPagination, paginationCmd ) =
                    Pagination.jumpTo paginationConfig model.communities page
            in
                ( { updatedModel | communities = updatedPagination }
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

        updatedSearchString =
            Routing.getSearchFilter filters |> Maybe.withDefault ""
    in
        if filters /= communityFilters || ordering /= communityOrdering then
            Pagination.updateData paginationConfig model.communities updatedRequestData
                |> (\( m, c ) ->
                        ( { updatedModel | communities = m }, Cmd.map CommunityPagination c )
                   )
        else if page /= Pagination.getPage model.communities then
            jumpToPage page
        else
            ( model, Cmd.none )


{-| Update the Model Based According to Some Message.
-}
update : Msg -> Model -> ( Model, Cmd Msg )
update msg model =
    case msg of
        SetCurrentDate currentDate ->
            ( { model | currentDate = Just currentDate }, Cmd.none )

        UrlChange newRoute ->
            updateUrl newRoute model

        NavigateTo newRoute ->
            ( model, Commands.newPage newRoute )

        UpdateSearchString newString ->
            ( { model | searchString = newString }
            , Cmd.none
            )

        SubmitSearchForm ->
            let
                newRoute =
                    Routing.mapFilters replaceSearchFilter model.route
                        |> Routing.mapPage (always 1)

                replaceSearchFilter filters =
                    case filters of
                        (SearchFilter _) :: fs ->
                            SearchFilter model.searchString :: fs

                        f :: fs ->
                            f :: replaceSearchFilter fs

                        [] ->
                            [ SearchFilter model.searchString ]
            in
                ( model, Commands.newPage newRoute )

        CommunityPagination subMsg ->
            let
                ( paginationModel, paginationCmd ) =
                    Pagination.update subMsg model.communities
            in
                ( { model | communities = paginationModel }
                , Cmd.map CommunityPagination paginationCmd
                )
